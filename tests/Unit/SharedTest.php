<?php

namespace Tests\Unit;

use Indgy\Container\Container;
use Tests\Assets\Counter;


test('can share an instance', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(0))
        ->share(true)
        ->alias('Counter');

    $first = $c->get('Counter');
    expect($first->increment())->toBe(1);

    $second = $c->get('Counter');
    expect($second->increment())->toBe(2);

    $third = $c->get('Counter');
    expect($third->increment())->toBe(3);

    expect($first->increment())->toBe(4);

});
test('cannot call share() out of turn', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(0));

    $counter = $c->get(Counter::class);
    expect($counter->increment())->toBe(1);

    $this->expectException(\BadMethodCallException::class);
    $this->expectExceptionMessage('Cannot call share() before calling set(), call `set()->share()` instead');
    $c->share(true);

});
