<?php

namespace Tests\Unit;

use Indgy\Container\Container;
use Tests\Assets\Foo;


test('can set and get an instance from a function', function () {

    $c = new Container;
    $c->set(Foo::class, fn() => new Foo());

    expect($c->get(Foo::class))->toBeInstanceOf(Foo::class);

});
test('can set and get an instance from a string', function () {

    $c = new Container;
    $c->set(Foo::class, Foo::class);

    expect($c->get(Foo::class))->toBeInstanceOf(Foo::class);

});
test('can set and get an instance from a string with shortcut', function () {

    $c = new Container;
    $c->set('Foo', Foo::class);

    expect($c->get('Foo'))->toBeInstanceOf(Foo::class);

});
test('has() returns true for defined instance', function () {

    $c = new Container;
    $c->set('Foo', Foo::class);

    expect($c->has('Foo'))->toBe(true);
    expect($c->has('AnythingElse'))->toBe(false);

});
test('has() returns true for alias', function () {

    $c = new Container;
    $c->set(Foo::class, Foo::class)
        ->alias('Bar');

    expect($c->has(Foo::class))->toBe(true);
    expect($c->has('Bar'))->toBe(true);
    expect($c->has('AnythingElse'))->toBe(false);

});
