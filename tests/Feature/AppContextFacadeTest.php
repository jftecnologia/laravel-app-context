<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelAppContext\ContextManager;
use JuniorFontenele\LaravelAppContext\Facades\AppContext;
use JuniorFontenele\LaravelAppContext\Providers\TimestampProvider;

describe('AppContext Facade', function () {
    beforeEach(function () {
        // Limpar o contexto antes de cada teste
        AppContext::clear();
    });

    it('resolves to ContextManager', function () {
        $facade = AppContext::getFacadeRoot();

        expect($facade)->toBeInstanceOf(ContextManager::class);
    });

    it('can call all() method through facade', function () {
        AppContext::addProvider(new TimestampProvider());

        $context = AppContext::all();

        expect($context)->toBeArray();
        expect($context)->toHaveKey('timestamp');
    });

    it('can call get() method through facade', function () {
        AppContext::addProvider(new TimestampProvider());

        $timestamp = AppContext::get('timestamp');

        expect($timestamp)->toBeString();
    });

    it('can call set() method through facade', function () {
        AppContext::resolveContext(); // Resolve primeiro
        AppContext::set('custom.key', 'facade-value');

        expect(AppContext::get('custom.key'))->toBe('facade-value');
    });

    it('can call clear() method through facade', function () {
        // Este teste verifica se o clear() limpa o contexto manual
        // mas não os providers registrados (que são parte da configuração)

        // Adiciona valor manual
        AppContext::resolveContext();
        AppContext::set('manual.test', 'value');
        expect(AppContext::has('manual.test'))->toBeTrue();

        // Clear deve limpar tudo (incluindo valores manuais)
        $result = AppContext::clear();

        expect($result)->toBeInstanceOf(ContextManager::class);

        // Após clear, valores manuais devem sumir
        // mas o contexto pode ter providers do service provider
        expect(AppContext::has('manual.test'))->toBeFalse();
    });

    it('can call addProvider() method through facade', function () {
        $provider = new TimestampProvider();

        $result = AppContext::addProvider($provider);

        expect($result)->toBeInstanceOf(ContextManager::class);
    });

    it('can call resolveContext() method through facade', function () {
        AppContext::clear();
        AppContext::addProvider(new TimestampProvider());

        $result = AppContext::resolveContext();

        expect($result)->toBeInstanceOf(ContextManager::class);
        expect(AppContext::all())->toHaveKey('timestamp');
    });

    it('can chain methods through facade', function () {
        AppContext::clear()
            ->resolveContext() // Resolve primeiro
            ->set('key1', 'value1')
            ->set('key2', 'value2');

        expect(AppContext::get('key1'))->toBe('value1');
        expect(AppContext::get('key2'))->toBe('value2');
    });

    it('returns default value when key not found', function () {
        AppContext::clear();

        expect(AppContext::get('nonexistent', 'default'))->toBe('default');
    });

    it('handles nested keys through facade', function () {
        AppContext::clear();
        AppContext::resolveContext(); // Resolve primeiro
        AppContext::set('nested.deep.key', 'nested-value');

        expect(AppContext::get('nested.deep.key'))->toBe('nested-value');
    });
});
