<?php

namespace Tests\Unit;

use Indgy\Container\Container;
use Tests\Assets\Counter;


test('can create a unique instance', function () {

    $c = new Container;
    $c->set(Counter::class, fn() => new Counter(0))
        ->share(false)
        ->alias('Counter');

    $first = $c->get('Counter');
    expect($first->increment())->toBe(1);

    $second = $c->get('Counter');
    expect($second->increment())->toBe(1);

    $third = $c->get('Counter');
    expect($third->increment())->toBe(1);

    expect($first->increment())->toBe(2);

});
