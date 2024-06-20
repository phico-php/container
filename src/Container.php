<?php

class Container
{
    protected array $items = [];
    protected array $aliases = [];
    private array $instances = [];
    private array $singletons = [];
    private string $cur = '';


    public function has(string $id): bool
    {
        // reset current working id
        $this->cur = '';

        // check items and aliases arrays
        return (array_key_exists($id, $this->items) OR array_key_exists($id, $this->aliases));
    }
    public function set(string $id, callable $fn): self
    {
        // set current working id
        $this->cur = $id;
        $this->items[$id] = [
            'singleton' => true,
            'call' => $fn
        ];
        return $this;
    }
    public function get(string $id): mixed
    {
        // reset current working id
        $this->cur = '';

        // get instance id from aliases if an alias is set
        if (array_key_exists($id, $this->aliases)) {
            $id = $this->aliases[$id];
        }
        // double check we know how to create the requested instance
        if ( ! $this->has($id)) {
            throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container as it cannot be found', $id));
        }

        // if singleton then return the existing instance
        if ($this->isSingleton($id)) {
            // create instance if we don't already have it
            if (!array_key_exists($id, $this->instances)) {
                $this->instances[$id] = $this->instantiate($id);
            }
            return $this->instances[$id];
        }

        // generate a new instance
        return $this->instantiate($id);
    }

    // support options

    public function alias(string $alias): self
    {
        $this->checkCur('alias');

        $this->aliases[$alias] = $this->cur;

        return $this;
    }
    public function singleton(bool $bool): self
    {
        $this->checkCur('singleton');

        $this->items[$this->cur]['singleton'] = $bool;

        return $this;
    }

    // handle singleton / multiton

    // return true if we should reuse the instance of $id
    public function isSingleton(string $id): bool
    {
        return array_key_exists($id, $this->singletons);
    }

    // create an instance
    private function instantiate($id): object
    {
        return $this->items[$id]['call']();
    }

    // support options methods, ensure we have a current id to work with
    private function checkCur(string $method): void
    {
        if (empty($this->cur)) {
            throw new BadMethodCallException(sprintf('Cannot call %1$s() before calling set(), call `set()->%1$s()` instead', $method));
        }
    }
}
