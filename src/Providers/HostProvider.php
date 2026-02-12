<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext\Providers;

class HostProvider extends AbstractProvider
{
    public function getContext(): array
    {
        $hostname = gethostname();

        return [
            'host' => [
                'name' => $hostname ?: 'unknown',
                'ip' => $hostname ? gethostbyname($hostname) : null,
            ],
        ];
    }
}
