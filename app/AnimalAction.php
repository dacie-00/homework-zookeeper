<?php
declare(strict_types=1);

namespace App;

class AnimalAction
{
    private $action;
    private int $times;
    private array $data;

    public function __construct(callable $action, int $times = 1, array $data = [])
    {
        $this->action = $action;
        $this->times = $times;
        $this->data = $data;
    }

    public function perform(): bool
    {
        if ($this->times === 1) {
            $this->data = [];
            return false;
        }
        ($this->action)($this->data);
        $this->times -= 1;
        return true;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function times(): int
    {
        return $this->times;
    }
}