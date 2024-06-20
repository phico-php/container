<?php

class Container
{
    protected array $items = [];
    protected array $aliases = [];


    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->items)) {
            return true;
        }
        if (array_key_exists($id, $this->aliases)) {
            return true;
        }

        return false;
    }
    public function set(string $id, callable $fn): self
    {
        $this->items[$id] = $fn;
        return $this;
    }
    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->aliases)) {
            $id = $this->aliases[$id];
        }
        if ( ! $this->has($id)) {
            throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container as it cannot be found', $id));
        }

        return $this->items[$id]();
    }

    // support aliases

    public function alias(string $from, string $to): self
    {
        $this->aliases[$from] = $to;
        return $this;
    }
}
