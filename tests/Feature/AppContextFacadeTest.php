<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\ContextManager;
use JuniorFontenele\LaravelContext\Contracts\ContextChannel;
use JuniorFontenele\LaravelContext\Contracts\ContextProvider;
use JuniorFontenele\LaravelContext\Facades\LaravelContext;
use JuniorFontenele\LaravelContext\Providers\TimestampProvider;

describe('LaravelContext Facade', function () {
    it('resolves to ContextManager', function () {
        $facade = LaravelContext::getFacadeRoot();

        expect($facade)->toBeInstanceOf(ContextManager::class);
    });

    it('can call all() method through facade', function () {
        LaravelContext::addProvider(new TimestampProvider());

        $context = LaravelContext::all();

        expect($context)->toBeArray();
        expect($context)->toHaveKey('timestamp');
    });

    it('can call get() method through facade', function () {
        LaravelContext::addProvider(new TimestampProvider());

        $timestamp = LaravelContext::get('timestamp');

        expect($timestamp)->toBeString();
    });

    it('can call set() method through facade', function () {
        LaravelContext::build(); // Resolve primeiro
        LaravelContext::set('custom.key', 'facade-value');

        expect(LaravelContext::get('custom.key'))->toBe('facade-value');
    });

    it('can call clear() method through facade', function () {
        // Este teste verifica se o clear() limpa o contexto manual
        // mas não os providers registrados (que são parte da configuração)

        // Adiciona valor manual
        LaravelContext::build();
        LaravelContext::set('manual.test', 'value');
        expect(LaravelContext::has('manual.test'))->toBeTrue();

        // Clear deve limpar tudo (incluindo valores manuais)
        $result = LaravelContext::clear();

        expect($result)->toBeInstanceOf(ContextManager::class);

        // Após clear, valores manuais devem sumir
        // mas o contexto pode ter providers do service provider
        expect(LaravelContext::has('manual.test'))->toBeFalse();
    });

    it('can call addProvider() method through facade', function () {
        $provider = new TimestampProvider();

        $result = LaravelContext::addProvider($provider);

        expect($result)->toBeInstanceOf(ContextManager::class);
    });

    it('can call build() method through facade', function () {
        LaravelContext::clear();
        LaravelContext::addProvider(new TimestampProvider());

        $result = LaravelContext::build();

        expect($result)->toBeInstanceOf(ContextManager::class);
        expect(LaravelContext::all())->toHaveKey('timestamp');
    });

    it('can chain methods through facade', function () {
        LaravelContext::clear()
            ->build() // Resolve primeiro
            ->set('key1', 'value1')
            ->set('key2', 'value2');

        expect(LaravelContext::get('key1'))->toBe('value1');
        expect(LaravelContext::get('key2'))->toBe('value2');
    });

    it('returns default value when key not found', function () {
        LaravelContext::clear();

        expect(LaravelContext::get('nonexistent', 'default'))->toBe('default');
    });

    it('handles nested keys through facade', function () {
        LaravelContext::clear();
        LaravelContext::build(); // Resolve primeiro
        LaravelContext::set('nested.deep.key', 'nested-value');

        expect(LaravelContext::get('nested.deep.key'))->toBe('nested-value');
    });

    it('can call rebuild() method through facade', function () {
        LaravelContext::clear();
        LaravelContext::addProvider(new TimestampProvider());
        LaravelContext::build();

        $result = LaravelContext::rebuild();

        expect($result)->toBeInstanceOf(ContextManager::class);
        expect(LaravelContext::all())->toHaveKey('timestamp');
    });

    it('can call sendContextToChannels() method through facade', function () {
        $channel = Mockery::mock(ContextChannel::class);
        $channel->shouldReceive('registerContext')
            ->twice()
            ->with(Mockery::on(function ($context) {
                return is_array($context) && isset($context['test']) && $context['test'] === 'value';
            }));

        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        LaravelContext::clear();
        LaravelContext::addProvider($provider);
        LaravelContext::addChannel($channel);
        LaravelContext::build();

        LaravelContext::sendContextToChannels();
    });

    it('can call clearProviderCache() method through facade', function () {
        LaravelContext::clear();
        LaravelContext::addProvider(new TimestampProvider());
        LaravelContext::build();

        $result = LaravelContext::clearProviderCache(TimestampProvider::class);

        expect($result)->toBeInstanceOf(ContextManager::class);
    });
});
