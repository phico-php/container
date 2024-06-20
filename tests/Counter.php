<?php

class Counter
{
    private int $counter;


    public function __construct(int $counter)
    {
        $this->counter = $counter;
    }

    public function increment(): string
    {
        $this->counter++;

        return sprintf("\nCounted to %d\n", $this->counter);
    }
}
