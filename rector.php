<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withSets([
        LevelSetList::UP_TO_PHP_74,
        SymfonySetList::SYMFONY_50,
        SymfonySetList::SYMFONY_51,
        SymfonySetList::SYMFONY_52,
        SymfonySetList::SYMFONY_53,
        SymfonySetList::SYMFONY_54,
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/var/cache',
        OptionalParametersAfterRequiredRector::class, // this re-orders parameters and requires updating calls to those functions. this would produce backward-incompatible changes
    ])
    ->withPaths([__DIR__]);
