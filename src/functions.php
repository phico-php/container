<?php

declare(strict_types=1);

// these functions cannot be overridden at the moment
function container(): \Phico\Container\Container
{
    static $container;
    $container = ($container) ? $container : new \Phico\Container\Container();
    return $container;
}
