<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateTaskRequest;
use App\Dto\UpdateTaskRequest;
use App\Exception\ValidationException;
use App\Http\ApiResponse;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/tasks', name: 'api_task_')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskService $taskService,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page   = max(1, (int) $request->query->get('page', 1));
        $limit  = max(1, min(100, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status');

        $result = $this->taskService->getAllTasks($page, $limit, $status);

        return ApiResponse::success('Task list', [
            'items' => array_map(fn($task) => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'status' => $task->getStatus(),
                'description' => $task->getDescription(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $result->items),
            'pagination' => [
                'total' => $result->total,
                'page' => $result->page,
                'limit' => $result->limit,
                'pages' => $result->pages,
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskByIdOrFail($id);

        return ApiResponse::success(
            'Get task',
            $this->serializer->normalize($task, null, ['groups' => 'task:read'])
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $data = $this->decodeJsonOrFail($request);
        $dto  = CreateTaskRequest::fromArray($data, $userId);

        $task = $this->taskService->createTaskFromDto($dto);

        return ApiResponse::success(
            'Task created successfully',
            $this->serializer->normalize($task, null, ['groups' => 'task:read']),
            201
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $data = $this->decodeJsonOrFail($request);
        $dto  = UpdateTaskRequest::fromArray($data, $userId);

        $task = $this->taskService->updateTask($id, $dto);

        return ApiResponse::success(
            'Task updated successfully',
            $this->serializer->normalize($task, null, ['groups' => 'task:read'])
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->taskService->deleteTaskById($id);
        return ApiResponse::success('Task deleted successfully', null, 204);
    }

    private function decodeJsonOrFail(Request $request): array
    {
        $raw  = (string) $request->getContent();
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException('Invalid JSON: ' . json_last_error_msg());
        }
        return $data ?? [];
    }
}
