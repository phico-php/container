<?php

namespace Tests\Assets;

class A
{
    private B $b;


    public function __construct(B $b)
    {
        $this->b = $b;
    }
    public function b(): B
    {
        return $this->b;
    }
}
