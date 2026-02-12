<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Context;
use JuniorFontenele\LaravelContext\Channels\LogChannel;

describe('LogChannel', function () {
    it('registers context in Laravel Context facade', function () {
        Context::shouldReceive('add')
            ->once()
            ->with([
                'test' => 'value',
                'nested' => [
                    'key' => 'data',
                ],
            ]);

        $channel = new LogChannel();
        $channel->registerContext([
            'test' => 'value',
            'nested' => [
                'key' => 'data',
            ],
        ]);
    });

    it('can register empty context', function () {
        Context::shouldReceive('add')
            ->once()
            ->with([]);

        $channel = new LogChannel();
        $channel->registerContext([]);

        // Assert que o método foi chamado
        expect(true)->toBeTrue();
    });

    it('implements ContextChannel interface', function () {
        $channel = new LogChannel();

        expect($channel)->toBeInstanceOf(JuniorFontenele\LaravelContext\Contracts\ContextChannel::class);
    });
});
