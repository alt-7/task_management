<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateTaskRequest;
use App\Dto\PaginatedResult;
use App\Dto\UpdateTaskRequest;
use App\Entity\Task;
use App\Exception\ValidationException;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskRepository $tasks,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {}

    public function getAllTasks(int $page = 1, int $limit = 10, ?string $status = null): PaginatedResult
    {
        if ($status !== null && !in_array($status, ['new', 'in_progress', 'completed'], true)) {
            throw new ValidationException('Invalid status filter');
        }

        $page  = max(1, $page);
        $limit = min(max(1, $limit), 100);

        return $this->tasks->findWithPagination($page, $limit, $status);
    }

    public function getTaskByIdOrFail(int $id): Task
    {
        $task = $this->tasks->find($id);
        if (!$task) {
            $this->logger->info('Task not found', ['id' => $id]);
            throw new NotFoundHttpException('Task not found');
        }
        return $task;
    }

    public function createTaskFromDto(CreateTaskRequest $dto): Task
    {
        $this->assertDtoValid($dto);

        $task = (new Task())
            ->setTitle($dto->title)
            ->setDescription($dto->description)
            ->setStatus($dto->status)
            ->setCreatedBy($dto->createdBy);

        $this->assertEntityValid($task);

        $this->em->persist($task);
        $this->em->flush();

        $this->logger->info('Task created', ['id' => $task->getId()]);

        return $task;
    }

    public function updateTask(int $id, UpdateTaskRequest $dto): Task
    {
        $task = $this->getTaskByIdOrFail($id);

        if (!$dto->hasUpdates()) {
            throw new ValidationException('No fields to update provided');
        }

        $this->assertDtoValid($dto);

        if ($dto->title !== null) {
            $task->setTitle($dto->title);
        }
        if ($dto->description !== null) {
            $task->setDescription($dto->description);
        }
        if ($dto->status !== null) {
            $task->setStatus($dto->status);
        }

        $task->setUpdatedBy($dto->updatedBy);
        $this->assertEntityValid($task);

        $this->em->flush();
        $this->logger->info('Task updated', ['id' => $task->getId()]);

        return $task;
    }

    public function deleteTaskById(int $id): void
    {
        $task = $this->getTaskByIdOrFail($id);

        $this->em->remove($task);
        $this->em->flush();

        $this->logger->info('Task deleted', ['id' => $id]);
    }

    private function assertDtoValid(object $dto): void
    {
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationException('Validation failed', $this->collectViolations($violations));
        }
    }

    private function assertEntityValid(Task $task): void
    {
        $violations = $this->validator->validate($task);
        if (count($violations) > 0) {
            throw new ValidationException('Validation failed', $this->collectViolations($violations));
        }
    }

    private function collectViolations(ConstraintViolationListInterface $list): array
    {
        $errors = [];
        foreach ($list as $v) {
            $path = (string) $v->getPropertyPath();
            $errors[$path] = $v->getMessage();
        }
        return $errors;
    }
}
