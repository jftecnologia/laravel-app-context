<?php

declare(strict_types = 1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withSetProviders(LaravelSetProvider::class)
    ->withSets([
        SetList::CODE_QUALITY,
    ])
    ->withComposerBased(laravel: true)
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        CompactToVariablesRector::class,
    ])
    ->withCache(__DIR__ . '/storage/rector', FileCacheStorage::class);
