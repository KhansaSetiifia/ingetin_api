<?php

namespace App\Repositories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Psr\Log\LoggerInterface;

class TodoRepository
{
    private $todo;
    private $logger;

    public function __construct(Todo $todo, LoggerInterface $logger)
    {
        $this->todo = $todo;
        $this->logger = $logger;
    }

    public function getAll()
    {
        try {
            return $this->todo->all();
        } catch (QueryException $e) {
            $this->logger->error('Error getting all todos: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todos.');
        }
    }

    public function getAllByUserId(int $userId)
    {
        try {
            return $this->todo->where('user_id', $userId)->get();
        } catch (QueryException $e) {
            $this->logger->error('Error getting todos by user ID: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todos.');
        }
    }

    public function getById(int $id)
    {
        try {
            return $this->todo->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->warning('Todo not found: ' . $e->getMessage());
            throw new \Exception('Todo not found.');
        } catch (QueryException $e) {
            $this->logger->error('Error getting todo by ID: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todo.');
        }
    }

    public function create(array $todoData)
    {
        try {
            return $this->todo->create($todoData);
        } catch (QueryException $e) {
            $this->logger->error('Error creating todo: ' . $e->getMessage());
            throw new \Exception('Failed to create todo.');
        }
    }

    public function update(int $id, array $todoData)
    {
        try {
            $todo = $this->todo->findOrFail($id);
            $todo->update($todoData);
            return $todo;
        } catch (ModelNotFoundException $e) {
            $this->logger->warning('Todo not found: ' . $e->getMessage());
            throw new \Exception('Todo not found.');
        } catch (QueryException $e) {
            $this->logger->error('Error updating todo: ' . $e->getMessage());
            throw new \Exception('Failed to update todo.');
        }
    }

    public function delete(int $id)
    {
        try {
            $todo = $this->todo->findOrFail($id);
            $todo->delete();
        } catch (ModelNotFoundException $e) {
            $this->logger->warning('Todo not found: ' . $e->getMessage());
            throw new \Exception('Todo not found.');
        } catch (QueryException $e) {
            $this->logger->error('Error deleting todo: ' . $e->getMessage());
            throw new \Exception('Failed to delete todo.');
        }
    }
    
    public function getByCategory(int $userId, int $categoryId)
    {
        try {
            return $this->todo->where('user_id', $userId)
                            ->where('category_id', $categoryId)
                            ->get();
        } catch (QueryException $e) {
            $this->logger->error('Error getting todos by category: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todos by category.');
        }
    }
    
    public function getByStatus(int $userId, string $status)
    {
        try {
            return $this->todo->where('user_id', $userId)
                            ->where('status', $status)
                            ->get();
        } catch (QueryException $e) {
            $this->logger->error('Error getting todos by status: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todos by status.');
        }
    }
    
    public function getByPriority(int $userId, string $priority)
    {
        try {
            return $this->todo->where('user_id', $userId)
                            ->where('priority', $priority)
                            ->get();
        } catch (QueryException $e) {
            $this->logger->error('Error getting todos by priority: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve todos by priority.');
        }
    }
    
    public function searchByKeyword(int $userId, string $keyword)
    {
        try {
            return $this->todo->where('user_id', $userId)
                            ->where(function($query) use ($keyword) {
                                $query->where('title', 'LIKE', "%{$keyword}%")
                                      ->orWhere('description', 'LIKE', "%{$keyword}%");
                            })
                            ->get();
        } catch (QueryException $e) {
            $this->logger->error('Error searching todos: ' . $e->getMessage());
            throw new \Exception('Failed to search todos.');
        }
    }
}
