<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Services\TodoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    private $todoService;
    private $logger;
    private $request;

    public function __construct(TodoService $todoService, LoggerInterface $logger, Request $request)
    {
        $this->todoService = $todoService;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function index(): JsonResponse
    {
        try {
            $todos = $this->todoService->getAllTodos();
            return $this->jsonResponse(Response::HTTP_OK, 'Todos retrieved successfully', $todos);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $todo = $this->todoService->getTodoById($id);
            return $this->jsonResponse(Response::HTTP_OK, 'Todo retrieved successfully', $todo);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_NOT_FOUND, $e->getMessage());
        }
    }

    public function store(CreateTodoRequest $request): JsonResponse
    {
        try {
            $todo = $this->todoService->createTodo($request->validated());
            return $this->jsonResponse(Response::HTTP_CREATED, 'Todo created successfully', $todo);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function update(UpdateTodoRequest $request, int $id): JsonResponse
    {
        try {
            $todo = $this->todoService->updateTodo($id, $request->validated());
            return $this->jsonResponse(Response::HTTP_OK, 'Todo updated successfully', $todo);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->todoService->deleteTodo($id);
            return $this->jsonResponse(Response::HTTP_OK, 'Todo deleted successfully', ['id' => $id]);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    
    public function getByCategory(int $categoryId): JsonResponse
    {
        try {
            $todos = $this->todoService->getTodosByCategory($categoryId);
            return $this->jsonResponse(Response::HTTP_OK, 'Todos by category retrieved successfully', $todos);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $todos = $this->todoService->getTodosByStatus($status);
            return $this->jsonResponse(Response::HTTP_OK, 'Todos by status retrieved successfully', $todos);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    
    public function getByPriority(string $priority): JsonResponse
    {
        try {
            $todos = $this->todoService->getTodosByPriority($priority);
            return $this->jsonResponse(Response::HTTP_OK, 'Todos by priority retrieved successfully', $todos);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    
    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = $request->query('q');
            if (empty($keyword)) {
                throw new \Exception('Search keyword is required');
            }
            $todos = $this->todoService->searchTodos($keyword);
            return $this->jsonResponse(Response::HTTP_OK, 'Todos search results', $todos);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
    
    public function debugAuth(): JsonResponse
    {
        try {
            $data = [
                'auth_id' => Auth::id(),
                'auth_check' => Auth::check(),
                'request_user_id' => $this->request->authenticated_user_id ?? 'not set',
                'token' => $this->request->header('token')
            ];
            
            return $this->jsonResponse(Response::HTTP_OK, 'Auth debug info', $data);
        } catch (\Exception $e) {
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
    
    protected function jsonResponse(int $statusCode, string $message, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
