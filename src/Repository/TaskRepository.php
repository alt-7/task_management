<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use App\Dto\PaginatedResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Пагинированный список задач с фильтрацией по статусу
     * @param int $page
     * @param int $limit
     * @param string|null $status
     * @return PaginatedResult
     */
    public function findWithPagination(int $page = 1, int $limit = 10, ?string $status = null): PaginatedResult
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        $offset = ($page - 1) * $limit;

        $query = $qb->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);
        $total = count($paginator);
        $pages = (int)ceil($total / $limit);

        return new PaginatedResult(
            iterator_to_array($paginator),
            $total,
            $page,
            $limit,
            $pages
        );
    }
}
