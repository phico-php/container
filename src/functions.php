<?php

declare(strict_types=1);

// these functions cannot be overridden at the moment
function container(array $config = []): \Phico\Container\Container
{
    if (empty($config)) {
        $config = config()->get('container');
    }

    static $container;
    $container = ($container) ? $container : new \Phico\Container\Container($config);
    return $container;
}
