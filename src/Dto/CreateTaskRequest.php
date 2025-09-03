<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Task;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTaskRequest
{
    #[Assert\NotBlank(message: "Title is required")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long",
        maxMessage: "Title cannot be longer than {{ limit }} characters"
    )]
    public string $title;

    #[Assert\Length(
        max: 1000,
        maxMessage: "Description cannot be longer than {{ limit }} characters"
    )]
    public ?string $description;

    #[Assert\Choice(
        choices: ['pending', 'in_progress', 'completed'],
        message: "Status must be one of: pending, in_progress, completed"
    )]
    public string $status;
    public int $createdBy = 1;

    public function __construct(
        string $title,
        ?string $description = null,
        string $status = Task::STATUS_PENDING,
        int $createdBy = 1,
    ) {
        $this->title = trim($title);
        $this->description = $description ? trim($description) : null;
        $this->status = $status;
    }

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            $data['title'] ?? '',
            $data['description'] ?? null,
            $data['status'] ?? Task::STATUS_PENDING,
            $userId,
        );
    }
}
