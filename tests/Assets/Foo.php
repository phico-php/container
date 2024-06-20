<?php

namespace Tests\Assets;

class Foo
{
    private string $name;


    public function __construct(string $str = 'Foo')
    {
        $this->name = $str;
    }
    public function do(): string
    {
        return 'Foo do? Who do?';
    }
}
