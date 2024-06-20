<?php

class Bar
{
    private string $name;


    public function __construct(string $str)
    {
        $this->name = $str ?? 'bar';
    }

    public function hello(): string
    {
        return 'Hi from the bar';
    }
}
