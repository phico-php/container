<?php

class Container
{
    protected array $items = [];


    public function has(string $id): bool
    {
        return array_key_exists($id, $this->items);
    }
    public function set(string $id, callable $fn): self
    {
        $this->items[$id] = $fn;
        return $this;
    }
    public function get(string $id): mixed
    {
        return $this->items[$id]();
    }
}
