<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\Providers\AppProvider;
use JuniorFontenele\LaravelContext\Providers\HostProvider;
use JuniorFontenele\LaravelContext\Providers\RequestProvider;
use JuniorFontenele\LaravelContext\Providers\TimestampProvider;
use JuniorFontenele\LaravelContext\Providers\UserProvider;

describe('Providers Feature Tests', function () {
    describe('AppProvider', function () {
        it('returns app context information', function () {
            $provider = new AppProvider();
            $context = $provider->getContext();

            expect($context)->toHaveKey('app');
            expect($context['app'])->toHaveKeys([
                'name',
                'env',
                'debug',
                'url',
                'timezone',
                'locale',
                'origin',
            ]);
            expect($context['app']['env'])->toBe('testing');
        });

        it('detects console origin when running in console', function () {
            $provider = new AppProvider();
            $context = $provider->getContext();

            expect($context['app']['origin'])->toBe('console');
        });

        it('should always run', function () {
            $provider = new AppProvider();

            expect($provider->shouldRun())->toBeTrue();
        });
    });

    describe('HostProvider', function () {
        it('returns host context information', function () {
            $provider = new HostProvider();
            $context = $provider->getContext();

            expect($context)->toHaveKey('host');
            expect($context['host'])->toHaveKeys(['name', 'ip']);
            expect($context['host']['name'])->toBeString();
            expect($context['host']['name'])->not()->toBe('unknown');
        });

        it('should always run', function () {
            $provider = new HostProvider();

            expect($provider->shouldRun())->toBeTrue();
        });
    });

    describe('TimestampProvider', function () {
        it('returns timestamp in ISO 8601 format', function () {
            $provider = new TimestampProvider();
            $context = $provider->getContext();

            expect($context)->toHaveKey('timestamp');
            expect($context['timestamp'])->toBeString();
            expect($context['timestamp'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/');
        });

        it('should always run', function () {
            $provider = new TimestampProvider();

            expect($provider->shouldRun())->toBeTrue();
        });
    });

    describe('RequestProvider', function () {
        it('should not run when running in console', function () {
            $provider = new RequestProvider();

            expect($provider->shouldRun())->toBeFalse();
        });
    });

    describe('UserProvider', function () {
        it('should not run when user is not authenticated', function () {
            $provider = new UserProvider();

            expect($provider->shouldRun())->toBeFalse();
        });
    });
});
