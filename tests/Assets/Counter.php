<?php

namespace Tests\Assets;


class Counter
{
    private int $counter;


    public function __construct(int $counter)
    {
        $this->counter = $counter;
    }

    public function increment(): int
    {
        $this->counter++;

        return $this->counter;
    }
}
