<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTaskRequest
{
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long",
        maxMessage: "Title cannot be longer than {{ limit }} characters"
    )]
    public ?string $title;

    #[Assert\Length(
        max: 1000,
        maxMessage: "Description cannot be longer than {{ limit }} characters"
    )]
    public ?string $description;

    #[Assert\Choice(
        choices: ['pending', 'in_progress', 'completed'],
        message: "Status must be one of: pending, in_progress, completed"
    )]
    public ?string $status;

    public int $updatedBy = 1;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $status = null,
        int $updatedBy = 1,
    ) {
        $this->title = $title ? trim($title) : null;
        $this->description = $description ? trim($description) : null;
        $this->status = $status;
    }

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            $data['title'] ?? null,
            $data['description'] ?? null,
            $data['status'] ?? null,
                $userId
        );
    }

    public function hasUpdates(): bool
    {
        return $this->title !== null ||
            $this->description !== null ||
            $this->status !== null;
    }
}
