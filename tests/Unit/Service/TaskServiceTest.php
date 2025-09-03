<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\CreateTaskRequest;
use App\Dto\UpdateTaskRequest;
use App\Entity\Task;
use App\Exception\ValidationException;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @covers \App\Service\TaskService
 */
class TaskServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private TaskRepository $taskRepository;
    private ValidatorInterface $validator;
    private TaskService $taskService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->taskService = new TaskService(
            $this->em,
            $this->taskRepository,
            $this->validator,
            $logger
        );
    }

    public function testGetTaskByIdOrFailThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Task not found');
        $this->taskRepository->method('find')->with(1)->willReturn(null);

        $this->taskService->getTaskByIdOrFail(1);
    }

    public function testGetTaskByIdOrFailReturnsTaskWhenFound(): void
    {
        $task = new Task();
        $this->taskRepository->method('find')->with(1)->willReturn($task);

        $result = $this->taskService->getTaskByIdOrFail(1);

        $this->assertSame($task, $result);
    }

    public function testCreateTaskFromDtoSuccessfully(): void
    {
        $dto = new CreateTaskRequest(
            title: 'New Test Task',
            description: 'Test Description',
            status: 'pending'
        );

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $task = $this->taskService->createTaskFromDto($dto);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('New Test Task', $task->getTitle());
    }

    public function testCreateTaskFromDtoThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $dto = new CreateTaskRequest(
            title: '', // Невалидный title
            description: null,
            status: 'invalid_status' // Невалидный status
        );

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(1);
        $this->validator->method('validate')->willReturn($violations);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->taskService->createTaskFromDto($dto);
    }

    public function testUpdateTaskSuccessfully(): void
    {
        $existingTask = (new Task())->setTitle('Old Title');
        $this->taskRepository->method('find')->with(1)->willReturn($existingTask);

        $dto = new UpdateTaskRequest();
        $dto->title = 'Updated Title';

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->em->expects($this->once())->method('flush');

        $updatedTask = $this->taskService->updateTask(1, $dto);

        $this->assertEquals('Updated Title', $updatedTask->getTitle());
    }

    public function testUpdateTaskThrowsExceptionIfNoUpdatesProvided(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No fields to update provided');

        $existingTask = new Task();
        $this->taskRepository->method('find')->with(1)->willReturn($existingTask);

        $dto = new UpdateTaskRequest();

        $this->taskService->updateTask(1, $dto);
    }

    public function testDeleteTaskByIdSuccessfully(): void
    {
        $task = new Task();
        $this->taskRepository->method('find')->with(1)->willReturn($task);

        $this->em->expects($this->once())->method('remove')->with($task);
        $this->em->expects($this->once())->method('flush');

        $this->taskService->deleteTaskById(1);
    }
}
