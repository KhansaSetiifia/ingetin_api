<?php

namespace App\Services;

use App\Repositories\TodoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Auth;

class TodoService
{
    private $todoRepository;
    private $logger;
    private $request;

    public function __construct(TodoRepository $todoRepository, LoggerInterface $logger, Request $request)
    {
        $this->todoRepository = $todoRepository;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function getAllTodos()
    {
        try {
            return $this->todoRepository->getAllByUserId(Auth::id());
        } catch (\Exception $e) {
            $this->logger->error('Error in getAllTodos service: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTodoById(int $id)
    {
        try {
            $todo = $this->todoRepository->getById($id);
            if ($todo->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to todo');
            }
            return $todo;
        } catch (\Exception $e) {
            $this->logger->error('Error in getTodoById service: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createTodo(array $todoData)
    {
        try {
            DB::beginTransaction();
            
            // Get authenticated user ID from request or fallback to Auth::id()
            $userId = $this->request->authenticated_user_id ?? Auth::id();
            
            // Add debug logging
            $this->logger->info('Creating todo for user ID: ' . $userId);
            
            if (!$userId) {
                throw new \Exception('User ID not found. Authentication may not be working correctly.');
            }
            
            $todoData['user_id'] = $userId;
            $todo = $this->todoRepository->create($todoData);
            DB::commit();
            return $todo;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->error('Error in createTodo service: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateTodo(int $id, array $todoData)
    {
        try {
            DB::beginTransaction();
            $todo = $this->todoRepository->getById($id);
            if ($todo->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to todo');
            }
            $todo = $this->todoRepository->update($id, $todoData);
            DB::commit();
            return $todo;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->error('Error in updateTodo service: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteTodo(int $id)
    {
        try {
            DB::beginTransaction();
            $todo = $this->todoRepository->getById($id);
            
            // Get authenticated user ID from request or fallback to Auth::id()
            $userId = $this->request->authenticated_user_id ?? Auth::id();
            $this->logger->info('Attempt to delete todo: Todo ID='.$id.', Todo owner='.$todo->user_id.', Current user='.$userId);
            
            if ($todo->user_id != $userId) {
                $this->logger->warning('Unauthorized attempt to delete todo: Todo ID='.$id.', Todo owner='.$todo->user_id.', Current user='.$userId);
                throw new \Exception('Unauthorized access to todo');
            }
            
            $this->todoRepository->delete($id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->error('Error in deleteTodo service: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getTodosByCategory(int $categoryId)
    {
        try {
            return $this->todoRepository->getByCategory(Auth::id(), $categoryId);
        } catch (\Exception $e) {
            $this->logger->error('Error in getTodosByCategory service: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getTodosByStatus(string $status)
    {
        try {
            return $this->todoRepository->getByStatus(Auth::id(), $status);
        } catch (\Exception $e) {
            $this->logger->error('Error in getTodosByStatus service: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getTodosByPriority(string $priority)
    {
        try {
            return $this->todoRepository->getByPriority(Auth::id(), $priority);
        } catch (\Exception $e) {
            $this->logger->error('Error in getTodosByPriority service: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function searchTodos(string $keyword)
    {
        try {
            return $this->todoRepository->searchByKeyword(Auth::id(), $keyword);
        } catch (\Exception $e) {
            $this->logger->error('Error in searchTodos service: ' . $e->getMessage());
            throw $e;
        }
    }
}
