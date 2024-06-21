# Container

Small and focussed Dependency Injection container for the `Phico` framework.

## Installation

Using composer

```sh
composer require picophp/container
```

## Reference

Set a definition describing how to create the class
`set(string $classname, callable|string $defintion, array $args =[])`

Set a shortcut alias on a definition
`alias(string $name)`

Set the share flag on a definition
`share(bool $toggle)`

Returns an instatiated class
`get(string $classname or $alias)`


## Usage

```php
// pass an optional array of parameters through the constructor
$c = new Pico\Container([
    'autowiring' => false, // default is true
    'sharing' => false, // default is true
]);

// use get() to fetch an instantiated class
$foo = $c->get(Foo::class);
// foo is now an instance of Foo
```

### Autowiring

With autowiring enabled, Container will do it's best to find and construct the class without any explicit instructions.

When autowiring is disabled, Container will need definitions creating using `set()`

```php
// set() accepts a string for simple cases
$c->set(Foo::class, Foo::class);

// or callables for more complex requirements
$c->set(Foo::class, function() {
    return new Foo::class
});

// shorter function syntax is fine too
$c->set(Foo::class, fn() => new Foo());
```

### Aliases

Sometimes you just want to avoid typing, you can add an alias using `alias()` after the call to `set()`;

```php
// set an alias to Foo named Bar, now foo can be accessed by get(Bar)
$c->set(Foo::class, Foo::class)
    ->alias('Bar');
$bar = $c->get('Bar');
// bar is now an instance of Foo
```

### Sharing

Some classes are better when shared, the default is true to always share instances once they have been created,
however sometimes you will need an unique instance on every call to get(), toggle this behaviour using the `share()`
method, it accepts a boolean true or false and will only affect the current definition.

```php
$c->set(Foo::class, Foo::class)
    ->share(false);
$a = $c->get(Foo::class);
$b = $c->get(Foo::class);

// $a is not equal to $b, they are different instances
```

## Contributing

Container is considered feature complete, however if you discover any bugs or issues in it's behaviour or performance please create an issue, and if you are able a pull request with a fix.

Please make sure to update tests as appropriate.

For major changes, please open an issue first to discuss what you would like to change.


## License

[BSD-3-Clause](https://choosealicense.com/licenses/bsd-3-clause/)

