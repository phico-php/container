<?php

namespace Indgy\Container;

class Container
{
    protected array $items = [];
    private array $instances = [];
    private string $cur = '';


    public function has(string $id): bool
    {
        // reset current working id
        $this->cur = '';

        // check items and aliases arrays
        return array_key_exists($id, $this->items);
    }
    public function set(string $id, callable|string $call = '', array $args = []): self
    {
        // set current working id
        $this->cur = $id;
        // if call is empty use the id as the class name
        if (empty($call)) {
            $id = $call;
            if (!class_exists($id)) {
                throw new \InvalidArgumentException(sprintf('Cannot set(%s) as the class does not exist', $id));
            }
        }
        // set the parameters for this class
        $this->items[$id] = [
            'share' => false,
            'call' => $call,
            'args' => $args
        ];
        return $this;
    }
    public function get(string $id): mixed
    {
        // reset current working id
        $this->cur = '';

        // double check we know how to create the requested instance
        if (!$this->has($id)) {
            if (class_exists($id)) {
                return new $id();
            }
            throw new \InvalidArgumentException(sprintf('Cannot get(%s) from the container as it cannot be found', $id));
        }

        // if shared then return the existing instance
        if (true === $this->items[$id]['share']) {
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
        $this->checkForCur('alias');

        if (array_key_exists($alias, $this->instances)) {
            throw new \InvalidArgumentException(sprintf('Cannot set alias %s as it has already been defined', $alias));
        }

        $this->items[$alias] = $this->items[$this->cur];

        return $this;
    }
    public function share(bool $bool): self
    {
        $this->checkForCur('share');

        $this->items[$this->cur]['share'] = $bool;

        return $this;
    }

    // create an instance
    private function instantiate($id): object
    {
        $args = array_map(function ($arg) {
            return is_callable($arg) ? call_user_func($arg) : $this->get($arg);
        }, $this->items[$id]['args']);

        $call = $this->items[$id]['call'];

        if (is_string($call)) {
            if (class_exists($call)) {
                return new $call(...$args);
            }
        }
        if (is_callable($call)) {
            return $call(...$args);
        }

        throw new \RuntimeException("Cannot instantiate '%s', not a string or callable", $id);
    }

    // support options methods, ensure we have a current id to work with
    private function checkForCur(string $method): void
    {
        if (empty($this->cur)) {
            throw new \BadMethodCallException(sprintf('Cannot call %1$s() before calling set(), call `set()->%1$s()` instead', $method));
        }
    }
}
