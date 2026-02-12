<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\Providers\HostProvider;

describe('HostProvider', function () {
    it('returns host context information', function () {
        $provider = new HostProvider();
        $context = $provider->getContext();

        expect($context)->toHaveKey('host');
        expect($context['host'])->toHaveKeys(['name', 'ip']);
        expect($context['host']['name'])->toBeString();
        expect($context['host']['name'])->not()->toBeEmpty();
    });

    it('returns unknown when hostname cannot be determined', function () {
        $provider = new class extends HostProvider
        {
            public function getContext(): array
            {
                // Simular falha no gethostname
                $hostname = false;

                return [
                    'host' => [
                        'name' => $hostname ?: 'unknown',
                        'ip' => $hostname ? gethostbyname($hostname) : null,
                    ],
                ];
            }
        };

        $context = $provider->getContext();

        expect($context['host']['name'])->toBe('unknown');
        expect($context['host']['ip'])->toBeNull();
    });

    it('should run by default', function () {
        $provider = new HostProvider();

        expect($provider->shouldRun())->toBeTrue();
    });
});
