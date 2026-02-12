<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\ContextManager;

describe('LaravelContextServiceProvider', function () {
    it('registers the ContextManager in the container', function () {
        expect(app()->bound(ContextManager::class))->toBeTrue();
        expect(app()->bound('laravel-context'))->toBeTrue();
    });

    it('resolves ContextManager from container', function () {
        $manager = app(ContextManager::class);

        expect($manager)->toBeInstanceOf(ContextManager::class);
    });

    it('resolves ContextManager using alias', function () {
        $manager = app('laravel-context');

        expect($manager)->toBeInstanceOf(ContextManager::class);
    });

    it('merges config from package', function () {
        expect(config('laravel-context'))->toBeArray();
        expect(config('laravel-context'))->toHaveKey('enabled');
        expect(config('laravel-context'))->toHaveKey('providers');
        expect(config('laravel-context'))->toHaveKey('channels');
    });

    it('loads default providers configuration', function () {
        $providers = config('laravel-context.providers');

        expect($providers)->toBeArray();
        expect($providers)->not()->toBeEmpty();
        expect($providers)->toContain(JuniorFontenele\LaravelContext\Providers\TimestampProvider::class);
        expect($providers)->toContain(JuniorFontenele\LaravelContext\Providers\AppProvider::class);
        expect($providers)->toContain(JuniorFontenele\LaravelContext\Providers\HostProvider::class);
    });

    it('loads default channels configuration', function () {
        $channels = config('laravel-context.channels');

        expect($channels)->toBeArray();
        expect($channels)->not()->toBeEmpty();
        expect($channels)->toContain(JuniorFontenele\LaravelContext\Channels\LogChannel::class);
    });
});
