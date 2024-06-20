<?php

namespace Tests\Assets;

class B
{
    private C $c;


    public function __construct(C $c)
    {
        $this->c = $c;
    }
    public function c(): C
    {
        return $this->c;
    }
}
