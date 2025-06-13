<?php

namespace App\Http\Controllers;

use App\Repositories\TodoRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class AdminController extends Controller
{
    private $todoRepository;
    private $userRepository;
    private $logger;

    public function __construct(
        TodoRepository $todoRepository,
        UserRepository $userRepository,
        LoggerInterface $logger
    ) {
        $this->todoRepository = $todoRepository;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    /**
     * Get all todos for a specific user
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserTodos(int $userId): JsonResponse
    {
        try {
            // Validate if user exists
            $user = $this->userRepository->getById($userId);
            
            // Get todos for the user
            $todos = $this->todoRepository->getAllByUserId($userId);
            
            return $this->jsonResponse(Response::HTTP_OK, 'User todos retrieved successfully', [
                'user' => $user,
                'todos' => $todos
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in getUserTodos: ' . $e->getMessage());
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Get user's todos filtered by status
     *
     * @param int $userId
     * @param string $status
     * @return JsonResponse
     */
    public function getUserTodosByStatus(int $userId, string $status): JsonResponse
    {
        try {
            // Validate if user exists
            $user = $this->userRepository->getById($userId);
            
            // Get todos for the user by status
            $todos = $this->todoRepository->getByStatus($userId, $status);
            
            return $this->jsonResponse(Response::HTTP_OK, "User's {$status} todos retrieved successfully", [
                'user' => $user,
                'todos' => $todos
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in getUserTodosByStatus: ' . $e->getMessage());
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Get user's todos filtered by priority
     *
     * @param int $userId
     * @param string $priority
     * @return JsonResponse
     */
    public function getUserTodosByPriority(int $userId, string $priority): JsonResponse
    {
        try {
            // Validate if user exists
            $user = $this->userRepository->getById($userId);
            
            // Get todos for the user by priority
            $todos = $this->todoRepository->getByPriority($userId, $priority);
            
            return $this->jsonResponse(Response::HTTP_OK, "User's {$priority} priority todos retrieved successfully", [
                'user' => $user,
                'todos' => $todos
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in getUserTodosByPriority: ' . $e->getMessage());
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Get user's todos filtered by category
     *
     * @param int $userId
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getUserTodosByCategory(int $userId, int $categoryId): JsonResponse
    {
        try {
            // Validate if user exists
            $user = $this->userRepository->getById($userId);
            
            // Get todos for the user by category
            $todos = $this->todoRepository->getByCategory($userId, $categoryId);
            
            return $this->jsonResponse(Response::HTTP_OK, "User's todos by category retrieved successfully", [
                'user' => $user,
                'todos' => $todos
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in getUserTodosByCategory: ' . $e->getMessage());
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Search todos for a specific user
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUserTodos(int $userId, Request $request): JsonResponse
    {
        try {
            $keyword = $request->query('q');
            if (empty($keyword)) {
                throw new \Exception('Search keyword is required');
            }
            
            // Validate if user exists
            $user = $this->userRepository->getById($userId);
            
            // Search todos for the user
            $todos = $this->todoRepository->searchByKeyword($userId, $keyword);
            
            return $this->jsonResponse(Response::HTTP_OK, "User's todos search results", [
                'user' => $user,
                'todos' => $todos
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error in searchUserTodos: ' . $e->getMessage());
            return $this->jsonResponse(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    /**
     * JSON response helper
     *
     * @param int $statusCode
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    protected function jsonResponse(int $statusCode, string $message, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
