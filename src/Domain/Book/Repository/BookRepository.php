<?php

namespace App\Domain\Book\Repository;

use App\Domain\Book\Book;
use App\Domain\Category\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Book[]
     */
    public function findSimilar(Book ...$books): array
    {
        $sql = <<<SQL
SELECT book.id, book.title, book.isbn
FROM book
WHERE book.title IN (:titles)
UNION  # UNION instead of OR allows us to use indexes
SELECT book.id, book.title, book.isbn
FROM book 
WHERE book.isbn IN (:isbns)
SQL;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Book::class, 'book');
        $rsm->addFieldResult('book', 'id', 'id');
        $rsm->addFieldResult('book', 'title', 'title');
        $rsm->addFieldResult('book', 'isbn', 'isbn');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('titles', array_map(fn(Book $book) => $book->getTitle(), $books));
        $query->setParameter('isbns', array_map(fn(Book $book) => $book->getIsbn(), $books));

        return $query->getResult();
    }

    public function similarExists(Book ...$books): bool
    {
        return (bool)$this->findSimilar(...$books);
    }

    public function findByIdJoinCategoriesAndAuthors(int $id): ?Book
    {
        try {
            return $this->createQueryBuilder('b')
                ->addSelect('categories')
                ->addSelect('authors')
                ->leftJoin('b.categories', 'categories')
                ->leftJoin('b.authors', 'authors')
                ->where('b.id = :id')->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Unexpected domain.', previous: $e);
        }
    }

    /**
     * @param Book $book
     * @return Book[]
     */
    public function findBooksInSameCategory(Book $book): array
    {
        $qb = $this->createQueryBuilder('b');

        $qb->join('b.categories', 'categories')
            ->where('categories.name IN (:names)')->setParameter('names', $book->getCategoryNames())
            ->andWhere('b.id <> :id')->setParameter('id', $book->getId());

        return $qb->getQuery()->getResult();
    }

    public function getQueryForCategoryPagination(Category|int $category): Query
    {
        $id = $category instanceof Category ? $category->getId() : $category;

        $qb = $this->createQueryBuilder('b')
            ->join('b.categories', 'categories')
            ->where('categories.id = :id')
            ->setParameter('id', $id)
            ->orderBy('b.updatedAt', 'DESC');

        return $qb->getQuery();
    }
}
