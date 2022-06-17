<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    public function similarBookExists(string $title, ?string $isbn = ''): bool
    {
        $qb = $this->createQueryBuilder('b');

        $qb->select('COUNT(b.id)')
            ->where('b.title = :title')->setParameter('title', $title);

        if (null === $isbn) {
            $qb->orWhere('b.isbn IS NULL');
        } else {
            $qb->orWhere('b.isbn = :isbn')->setParameter('isbn', $isbn);
        }

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
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

    public function findBooksInSameCategory(Book $book): array
    {
        $qb = $this->createQueryBuilder('b');

        $qb->join('b.categories', 'categories')
            ->where('categories.name IN (:names)')->setParameter('names', $book->getCategoryNames())
            ->andWhere('b.id <> :id')->setParameter('id', $book->getId());

        return $qb->getQuery()->getResult();
    }
}
