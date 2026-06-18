<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides database access for Book entities.
 *
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    /**
     * Creates a repository for Book entities.
     *
     * @param ManagerRegistry $registry
     *
     * @return void
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Returns a paginated list of books, optionally filtered by author.
     *
     * @param string|null $author
     * @param int $page
     * @param int $perPage
     *
     * @return array{items: list<Book>, total: int}
     */
    public function findPaginatedByAuthor(?string $author, int $page, int $perPage): array
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $this->applyAuthorFilter($queryBuilder, $author);

        $total = (int) (clone $queryBuilder)
            ->select('COUNT(b.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $queryBuilder
            ->select('b')
            ->orderBy('b.id', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * Applies a case-insensitive partial match filter on the author field.
     *
     * @param QueryBuilder $queryBuilder
     * @param string|null $author
     *
     * @return void
     */
    private function applyAuthorFilter(QueryBuilder $queryBuilder, ?string $author): void
    {
        if ($author === null || $author === '') {
            return;
        }

        $queryBuilder
            ->andWhere('LOWER(b.author) LIKE LOWER(:author)')
            ->setParameter('author', '%'.$author.'%');
    }
}
