<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\ContextManager;
use JuniorFontenele\LaravelContext\Contracts\ContextChannel;
use JuniorFontenele\LaravelContext\Contracts\ContextProvider;

beforeEach(function () {
    $this->manager = new ContextManager();
});

describe('ContextManager', function () {
    it('can be instantiated', function () {
        expect($this->manager)->toBeInstanceOf(ContextManager::class);
    });

    it('can add a provider', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        $result = $this->manager->addProvider($provider);

        expect($result)->toBeInstanceOf(ContextManager::class);
    });

    it('can add a channel', function () {
        $channel = Mockery::mock(ContextChannel::class);

        $result = $this->manager->addChannel($channel);

        expect($result)->toBeInstanceOf(ContextManager::class);
    });

    it('resolves context from providers', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        $this->manager->addProvider($provider);
        $this->manager->build();
        $context = $this->manager->all();

        expect($context)->toHaveKey('test');
        expect($context['test'])->toBe('value');
    });

    it('skips providers that should not run', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(false);
        $provider->shouldReceive('isCacheable')->never();
        $provider->shouldReceive('getContext')->never();

        $this->manager->addProvider($provider);
        $this->manager->build();
        $context = $this->manager->all();

        expect($context)->toBeEmpty();
    });

    it('merges context from multiple providers', function () {
        $provider1 = Mockery::mock(ContextProvider::class);
        $provider1->shouldReceive('shouldRun')->andReturn(true);
        $provider1->shouldReceive('isCacheable')->andReturn(false); // Sem cache para este teste
        $provider1->shouldReceive('getContext')->andReturn(['key1' => 'value1']);

        $provider2 = Mockery::mock(ContextProvider::class);
        $provider2->shouldReceive('shouldRun')->andReturn(true);
        $provider2->shouldReceive('isCacheable')->andReturn(false); // Sem cache para este teste
        $provider2->shouldReceive('getContext')->andReturn(['key2' => 'value2']);

        $this->manager->addProvider($provider1);
        $this->manager->addProvider($provider2);
        $context = $this->manager->all();

        expect($context)->toHaveKeys(['key1', 'key2']);
        expect($context['key1'])->toBe('value1');
        expect($context['key2'])->toBe('value2');
    });

    it('sends context to channels after resolving', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        $channel = Mockery::mock(ContextChannel::class);
        $channel->shouldReceive('registerContext')
            ->once()
            ->with(['test' => 'value']);

        $this->manager->addProvider($provider);
        $this->manager->addChannel($channel);
        $this->manager->build();
    });

    it('returns all context data', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        $this->manager->addProvider($provider);
        $all = $this->manager->all();

        expect($all)->toHaveKey('test');
        expect($all['test'])->toBe('value');
    });

    it('gets a specific context value by key', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn([
            'nested' => [
                'key' => 'value',
            ],
        ]);

        $this->manager->addProvider($provider);

        expect($this->manager->get('nested.key'))->toBe('value');
    });

    it('returns default value when key not found', function () {
        expect($this->manager->get('nonexistent', 'default'))->toBe('default');
    });

    it('can set a context value', function () {
        // Primeiro resolve o contexto para marcar como resolvido
        $this->manager->build();

        $result = $this->manager->set('custom', 'value');

        expect($result)->toBeInstanceOf(ContextManager::class);
        expect($this->manager->get('custom'))->toBe('value');
    });

    it('can set nested context values', function () {
        // Primeiro resolve o contexto para marcar como resolvido
        $this->manager->build();

        $this->manager->set('nested.deep.key', 'value');

        expect($this->manager->get('nested.deep.key'))->toBe('value');
    });

    it('can clear the context', function () {
        // Primeiro resolve o contexto para marcar como resolvido
        $this->manager->build();

        $this->manager->set('test', 'value');
        expect($this->manager->get('test'))->toBe('value');

        $result = $this->manager->clear();

        expect($result)->toBeInstanceOf(ContextManager::class);

        // Após clear, o contexto deve estar vazio
        expect($this->manager->all())->toBeEmpty();
    });

    it('can rebuild from scratch clearing all cache', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->twice()->andReturn(['cached' => 'value']);

        $this->manager->addProvider($provider);
        $this->manager->build();

        // Primeiro acesso - provider é chamado
        expect($this->manager->get('cached'))->toBe('value');

        // rebuild() limpa cache - provider é chamado novamente
        $result = $this->manager->rebuild();

        expect($result)->toBeInstanceOf(ContextManager::class);
        expect($this->manager->get('cached'))->toBe('value');
    });

    it('can reset context and notify channels', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->andReturn(['test' => 'value']);

        $channel = Mockery::mock(ContextChannel::class);
        // Primeira chamada: build() com contexto normal
        $channel->shouldReceive('registerContext')
            ->once()
            ->with(['test' => 'value']);
        // Segunda chamada: reset() com contexto vazio
        $channel->shouldReceive('registerContext')
            ->once()
            ->with([]);

        $this->manager->addProvider($provider);
        $this->manager->addChannel($channel);
        $this->manager->build();

        // Reset deve limpar e notificar channels com contexto vazio
        $result = $this->manager->reset();

        expect($result)->toBeInstanceOf(ContextManager::class);
    });

    it('can clear cache for specific provider', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(true);
        $provider->shouldReceive('getContext')->once()->andReturn(['cached' => 'value']);

        $this->manager->addProvider($provider);
        $this->manager->build();

        // Primeiro acesso
        expect($this->manager->get('cached'))->toBe('value');

        // Limpar cache deste provider específico
        $providerClass = get_class($provider);
        $result = $this->manager->clearProviderCache($providerClass);

        expect($result)->toBeInstanceOf(ContextManager::class);

        // Verificar que marca contexto como não construído
        // (forçará rebuild no próximo acesso)
    });

    it('rebuild marks context as not built to trigger rebuild on next access', function () {
        $provider = Mockery::mock(ContextProvider::class);
        $provider->shouldReceive('shouldRun')->andReturn(true);
        $provider->shouldReceive('isCacheable')->andReturn(false);
        $provider->shouldReceive('getContext')->twice()->andReturn(['dynamic' => 'value']);

        $this->manager->addProvider($provider);
        $this->manager->build();

        // Primeiro all() - já buildado
        $this->manager->all();

        // Rebuild invalida flag built
        $this->manager->rebuild();

        // Próximo all() deve rebuildar (provider não cacheable é chamado novamente)
        $this->manager->all();
    });
});
