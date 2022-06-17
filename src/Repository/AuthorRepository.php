<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 *
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function add(Author $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Author $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string ...$names
     * @return Author[]
     */
    public function findAuthorsByName(string ...$names): array
    {
        $qb = $this->createQueryBuilder('a');

        $qb->where('a.name IN (:names)')->setParameter('names', $names);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string ...$names
     * @return string[]
     */
    public function findExistingNames(string ...$names): array
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a.name')
            ->where('a.name IN (:names)')
            ->setParameter('names', $names);

        return array_map(
            fn(array $data) => $data['name'],
            $qb->getQuery()->getResult()
        );
    }
}
