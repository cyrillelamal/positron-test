<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string ...$names
     * @return string[]
     */
    public function findExistingNames(string ...$names): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('c.name')
            ->where('c.name IN (:names)')
            ->setParameter('names', $names);

        return array_map(
            fn(array $data) => $data['name'],
            $qb->getQuery()->getResult()
        );
    }

    /**
     * @param string ...$names
     * @return Category[]
     */
    public function findByNames(string ...$names): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.name IN (:names)')->setParameter('names', $names);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Category[]
     */
    public function findTopLevelCategories(): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->getQuery()->getResult();
    }
}
