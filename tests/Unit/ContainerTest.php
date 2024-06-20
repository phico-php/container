<?php

namespace Tests\Unit;

use Indgy\Container\Container;
use Tests\Assets\{A,B,C,Foo,Bar,Plain};


test('can set and get an instance from a function', function () {

    $c = new Container;
    $c->set(Foo::class, fn() => new Foo());

    expect($c->get(Foo::class))->toBeInstanceOf(Foo::class);

    $c->set(Bar::class, function() {
        return new Bar('barbar');
    });

    expect($c->get(Foo::class))->toBeInstanceOf(Foo::class);

    expect($c->get(Bar::class))->toBeInstanceOf(Bar::class);
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
test('can instantiate nested classes', function () {

    $c = new Container;
    $c->set(C::class, C::class);
    $c->set(B::class, function() use ($c) {
        return new B($c->get(C::class));
    });
    $c->set(A::class, fn() => new A($c->get(B::class)));

    $a = $c->get(A::class);

    expect($a)->toBeInstanceOf(A::class);

    $b = $a->b();

    expect($b)->toBeInstanceOf(B::class);

    $c = $b->c();

    expect($c)->toBeInstanceOf(C::class);

});
test('can do magical autowiring', function () {

    $c = new Container;

    $a = $c->get(A::class);
    expect($a)->toBeInstanceOf(A::class);

    $b = $a->b();
    expect($b)->toBeInstanceOf(B::class);

    $c = $b->c();
    expect($c)->toBeInstanceOf(C::class);

    $c = new Container;

    $plain = $c->get(Plain::class);
    expect($plain)->toBeInstanceOf(Plain::class);

});
