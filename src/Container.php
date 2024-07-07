<?php

namespace Phico\Container;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;


class Container
{
    // holds the list of object creation definitions
    protected array $definitions = [];
    // holds the id being operated on during set()
    protected string $cur = '';
    // holds a list of created instances
    private array $instances = [];

    // user defined options
    // enable autowiring
    private bool $autowire;
    // default to sharing created instances
    private bool $sharing;
    // allowed options
    private array $options = [
        'autowire' => true,
        'sharing' => true,
    ];


    public function __construct(array $config = [])
    {
        // apply default options, overriding with user config
        foreach ($this->options as $k => $v) {
            $this->$k = (isset($config[$k])) ? $config[$k] : $v;
        }
    }
    // check a class can be instantiated
    public function has(string $id): bool
    {
        // reset current working id
        $this->cur = '';

        // check items and aliases arrays first
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }
        // if autowiring is enabled, we can create any class
        if ($this->autowire) {
            return class_exists($id);
        }
        // otherwise we can't
        return false;
    }
    // set a class creation definition
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
        $this->definitions[$id] = [
            'share' => $this->sharing,
            'call' => $call,
            'args' => $args
        ];

        return $this;
    }
    // get an instantiated class
    public function get(string $id): mixed
    {
        // reset current working id
        $this->cur = '';

        // is id in the list of created instances
        if (array_key_exists($id, $this->instances)) {
            return ($this->instances[$id]);
        }
        // is id in the list of definitions
        if (array_key_exists($id, $this->definitions)) {
            $obj = $this->instantiate($id);
            // store in instances to be shared on futher requests
            if ($this->definitions[$id]['share']) {
                $this->instances[$id] = $obj;
            }
            return $obj;
        }
        // attempt autowiring
        if (class_exists($id)) {
            if (true === $this->autowire) {
                return $this->autowire($id);
            }
            throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container, it has not been set(), the class can be found but autowiring is disabled, set autowire = true in your config to enable it', $id));
        }
        throw new InvalidArgumentException(sprintf('Cannot get(%s) from the container, it has not been set() and the class cannot be found', $id));
    }

    // support options

    // create an alias for this defintion
    public function alias(string $alias): self
    {
        $this->checkForCur('alias');

        if (array_key_exists($alias, $this->instances)) {
            throw new InvalidArgumentException(sprintf('Cannot call alias(%s) again as it has previously been defined', $alias));
        }

        $this->definitions[$alias] = $this->definitions[$this->cur];

        return $this;
    }
    // toggle sharing for this defintion
    public function share(bool $bool): self
    {
        $this->checkForCur('share');

        $this->definitions[$this->cur]['share'] = $bool;

        return $this;
    }

    // create an instance from an existing defintion
    private function instantiate($id): object
    {
        $args = array_map(function ($arg) {
            return is_callable($arg) ? call_user_func($arg) : $this->get($arg);
        }, $this->definitions[$id]['args']);

        $call = $this->definitions[$id]['call'];

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
    private function autowire($id)
    {
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
            if (!$type instanceof ReflectionNamedType or $type->isBuiltin()) {
                throw new RuntimeException(sprintf("Cannot instantiate '%s' as parameter '%s' cannot be resolved, please provide a constructor using set()", $id, $param->getName()));
            }
            // resolve the dependencys' dependencies
            $dependencies[] = $this->get($type->getName());
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
