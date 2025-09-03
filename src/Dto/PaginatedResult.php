<?php

declare(strict_types=1);

namespace App\Dto;

readonly class PaginatedResult
{
    /**
     * @param array<object> $items
     */
    public function __construct(
        public array $items,
        public int   $total,
        public int   $page,
        public int   $limit,
        public int   $pages
    ) {}
}
