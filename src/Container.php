<?php

namespace Phico\Container;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;


class Container
{
    protected array $items = [];
    protected string $cur = '';
    private array $instances = [];

    // user defined options
    private bool $autowiring = true;
    private bool $sharing = true;


    public function __construct(array $options = [])
    {
        foreach ($options as $k=>$v) {
            if (in_array($k, ['autowiring','sharing'])) {
                $this->$k = (bool) $v;
            }
        }
    }
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
                throw new InvalidArgumentException(sprintf('Cannot set(%s) as the class does not exist', $id));
            }
        }

        // set the parameters for this class
        $this->items[$id] = [
            'share' => $this->sharing,
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
                if (true === $this->autowiring) {
                    return $this->autowire($id);
                }
                throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container, it has not been set(), the class can be found but autowiring is disabled', $id));
            }
            throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container, it has not been set() and the class cannot be found', $id));
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
            throw new InvalidArgumentException(sprintf('Cannot call alias(%s) again as it has previously been defined', $alias));
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

        throw new RuntimeException("Cannot instantiate '%s', as it is not a string or a callable", $id);
    }

    // autowiring, yay!

    private function autowire($id) {
        $reflector = new ReflectionClass($id);

        // check for a constructor
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            // create the class without arguments
            return new $id();
        }

        // recursively resolve constructor params
        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof ReflectionNamedType OR $type->isBuiltin()) {
                throw new RuntimeException(sprintf("Cannot instantiate '%s' as parameter '%s' cannot be resolved, please provide a constructor using set()", $id, $param->getName()));
            }
            // resolve the dependencys' dependencies
            $dependencies[] = $this->autowire($type->getName());
        }

        // Create the class with the resolved dependencies
        return $reflector->newInstanceArgs($dependencies);
    }

    // support options methods, ensure we have a current id to work with
    private function checkForCur(string $method): void
    {
        if (empty($this->cur)) {
            throw new BadMethodCallException(sprintf('Cannot call %1$s() before calling set(), call `set()->%1$s()` instead', $method));
        }
    }
}
