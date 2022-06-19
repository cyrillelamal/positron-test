<?php

namespace App\Repository;

use App\Entity\Category;
use App\Service\PlatformInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository implements LoggerAwareInterface
{
    use PlatformInformation;

    private LoggerInterface $logger;

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

    public function upsert(Category ...$categories): void
    {
        if (!$this->isUsingMysql()) {
            throw new LogicException('Cannot execute native upsert query.');
        }
        if (empty($categories)) {
            return;
        }

        $sql = '';
        $params = [];
        for ($i = 0; $i < count($categories); $i++) {
            $sql .= "INSERT INTO `category` (`name`) VALUE (:name_$i) ON DUPLICATE KEY UPDATE `name` = `name`;"; // TODO: VALUES
            $params["name_$i"] = $categories[$i]->getName();
        }

        $connection = $this->getEntityManager()->getConnection();
        try {
            $statement = $connection->prepare($sql);
            $statement->executeStatement($params);
        } catch (Exception $e) {
            $this->logger->error('Invalid upsert query.', ['sql' => $sql, 'params' => $params]);
            throw new RuntimeException('Cannot upsert categories', previous: $e);
        }
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

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
