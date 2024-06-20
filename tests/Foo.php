<?php

class Foo
{
    private string $name;


    public function __construct(string $str)
    {
        $this->name = $str ?? 'foo';
    }
    public function do(): string
    {
        return 'Foo done';
    }
}
