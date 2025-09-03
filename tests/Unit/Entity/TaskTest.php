<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private Task $task;

    protected function setUp(): void
    {
        $this->task = new Task();
    }

    public function testTaskCreation(): void
    {
        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNull($this->task->getId());
        $this->assertEquals(Task::STATUS_PENDING, $this->task->getStatus());
    }

    public function testSetAndGetTitle(): void
    {
        $title = 'Test Task Title';
        $this->task->setTitle($title);
        $this->assertEquals($title, $this->task->getTitle());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'Test task description';
        $this->task->setDescription($description);
        $this->assertEquals($description, $this->task->getDescription());

        // Test null description
        $this->task->setDescription(null);
        $this->assertNull($this->task->getDescription());
    }

    public function testSetAndGetStatus(): void
    {
        $this->task->setStatus(Task::STATUS_IN_PROGRESS);
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $this->task->getStatus());

        $this->task->setStatus(Task::STATUS_COMPLETED);
        $this->assertEquals(Task::STATUS_COMPLETED, $this->task->getStatus());
    }

    public function testSetInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->task->setStatus('invalid_status');
    }

    public function testStatusCheckers(): void
    {
        $this->task->setStatus(Task::STATUS_PENDING);
        $this->assertTrue($this->task->isPending());
        $this->assertFalse($this->task->isInProgress());
        $this->assertFalse($this->task->isCompleted());

        $this->task->setStatus(Task::STATUS_IN_PROGRESS);
        $this->assertFalse($this->task->isPending());
        $this->assertTrue($this->task->isInProgress());
        $this->assertFalse($this->task->isCompleted());

        $this->task->setStatus(Task::STATUS_COMPLETED);
        $this->assertFalse($this->task->isPending());
        $this->assertFalse($this->task->isInProgress());
        $this->assertTrue($this->task->isCompleted());
    }

    public function testStatusMarkers(): void
    {
        $this->task->markAsInProgress();
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $this->task->getStatus());

        $this->task->markAsCompleted();
        $this->assertEquals(Task::STATUS_COMPLETED, $this->task->getStatus());

        $this->task->markAsPending();
        $this->assertEquals(Task::STATUS_PENDING, $this->task->getStatus());
    }

    public function testTimestamps(): void
    {
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $this->task->setCreatedAt($createdAt);
        $this->task->setUpdatedAt($updatedAt);

        $this->assertEquals($createdAt, $this->task->getCreatedAt());
        $this->assertEquals($updatedAt, $this->task->getUpdatedAt());
    }

    public function testLifecycleCallbacks(): void
    {
        $this->assertNull($this->task->getCreatedAt());
        $this->assertNull($this->task->getUpdatedAt());

        $this->task->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->task->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->task->getUpdatedAt());

        $oldUpdatedAt = $this->task->getUpdatedAt();

        // Simulate time passing
        usleep(1000);

        $this->task->onPreUpdate();
        $this->assertGreaterThan($oldUpdatedAt, $this->task->getUpdatedAt());
    }

    public function testValidStatuses(): void
    {
        $expectedStatuses = [
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_COMPLETED
        ];

        $this->assertEquals($expectedStatuses, Task::VALID_STATUSES);
    }

    public function testFluentInterface(): void
    {
        $result = $this->task
            ->setTitle('Test Title')
            ->setDescription('Test Description')
            ->setStatus(Task::STATUS_IN_PROGRESS);

        $this->assertSame($this->task, $result);
    }
}
