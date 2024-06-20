<?php

require 'src/Container.php';

require 'tests/Foo.php';
require 'tests/Bar.php';
require 'tests/Counter.php';

$c = new Container;

var_dump([
    'foo' => $c->has('Foo'),
    'bar' => $c->has('Bar'),
]);

$c->set(Counter::class, fn() => new Counter(1))->singleton(true);
$c->set(Foo::class, fn() => new Foo('foo'));
$c->set(Bar::class, function() {
    return new Bar('barred');
})->alias('BarOne');

var_dump([
    'fooOne' => $c->has('FooOne'),
    'foo' => $c->has('Foo'),
    'bar' => $c->has('Bar'),
    'barOne' => $c->has('BarOne'),
]);

$counter = $c->get('Counter');
echo $counter->increment();
echo $counter->increment();
echo $counter->increment();

var_dump($c->get('Foo'));
var_dump($c->get('Bar'));
var_dump($c->get('BarOne'));
// var_dump($c->get('BarOne'));

$counter2 = $c->get('Counter');
echo $counter2->increment();


$c->alias('muppet');
