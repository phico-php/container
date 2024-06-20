<?php

namespace Tests\Unit;

use Indgy\Container\Container;
use Tests\Assets\Counter;


test('can find an instance without an alias', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(0));

    $counter = $c->get(Counter::class);
    $counter->increment();
    $counter->increment();
    $counter->increment();

    expect($counter->increment())->toBe(4);

});
test('can find an instance with an alias', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(2))
        ->alias('Countr');

    $counter = $c->get('Countr');
    $counter->increment();
    $counter->increment();
    $counter->increment();

    expect($counter->increment())->toBe(6);

});
test('cannot call alias() out of turn', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(0));

    $counter = $c->get(Counter::class);
    expect($counter->increment())->toBe(1);

    $this->expectException(\BadMethodCallException::class);
    $this->expectExceptionMessage('Cannot call alias() before calling set(), call `set()->alias()` instead');
    $c->alias('Counter');

});
