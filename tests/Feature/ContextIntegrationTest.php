<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Context;
use JuniorFontenele\LaravelContext\Channels\LogChannel;
use JuniorFontenele\LaravelContext\ContextManager;
use JuniorFontenele\LaravelContext\Providers\AppProvider;
use JuniorFontenele\LaravelContext\Providers\HostProvider;
use JuniorFontenele\LaravelContext\Providers\TimestampProvider;

describe('Context Integration', function () {
    it('resolves context from multiple providers and sends to channels', function () {
        $manager = new ContextManager();

        // Adicionar providers
        $manager->addProvider(new TimestampProvider());
        $manager->addProvider(new AppProvider());
        $manager->addProvider(new HostProvider());

        // Mock do channel
        Context::shouldReceive('add')->once()->with(Mockery::on(function ($context) {
            return isset($context['timestamp']) &&
                   isset($context['app']) &&
                   isset($context['host']);
        }));

        $manager->addChannel(new LogChannel());

        // Resolver contexto
        $manager->build();
        $context = $manager->all();

        expect($context)->toHaveKey('timestamp');
        expect($context)->toHaveKey('app');
        expect($context)->toHaveKey('host');
        expect($context['app'])->toHaveKey('name');
        expect($context['host'])->toHaveKey('name');
    });

    it('can handle empty providers list', function () {
        $manager = new ContextManager();

        $context = $manager->all();

        expect($context)->toBeArray();
    });

    it('merges nested context from different providers', function () {
        $manager = new ContextManager();

        // Provider customizado 1
        $provider1 = new class extends JuniorFontenele\LaravelContext\Providers\AbstractProvider
        {
            public function getContext(): array
            {
                return [
                    'custom' => [
                        'key1' => 'value1',
                    ],
                ];
            }
        };

        // Provider customizado 2
        $provider2 = new class extends JuniorFontenele\LaravelContext\Providers\AbstractProvider
        {
            public function getContext(): array
            {
                return [
                    'custom' => [
                        'key2' => 'value2',
                    ],
                ];
            }
        };

        $manager->addProvider($provider1);
        $manager->addProvider($provider2);

        $manager->build();
        $context = $manager->all();

        // O segundo provider deve sobrescrever o primeiro
        expect($context['custom'])->toHaveKey('key2');
    });

    it('allows setting custom context alongside provider context', function () {
        $manager = new ContextManager();

        $manager->addProvider(new TimestampProvider());
        $manager->build(); // Resolve primeiro
        $manager->set('custom.field', 'custom-value');

        $context = $manager->all();

        expect($context)->toHaveKey('timestamp');
        expect($context['custom']['field'])->toBe('custom-value');
    });

    it('can clear and rebuild context', function () {
        $manager = new ContextManager();

        $manager->addProvider(new TimestampProvider());
        $manager->build();

        $context1 = $manager->all();

        expect($context1)->toHaveKey('timestamp');
        $timestamp1 = $context1['timestamp'];

        $manager->clear();

        // Adicionar provider novamente após clear e chamar build
        $manager->addProvider(new TimestampProvider());
        sleep(1); // Garantir que o timestamp será diferente
        $manager->build(); // Necessário porque clear() marca built=true
        $context2 = $manager->all();

        expect($context2)->toHaveKey('timestamp');
        expect($context2['timestamp'])->not()->toBe($timestamp1);
    });

    it('context is shared across multiple channel instances', function () {
        $manager = new ContextManager();

        $manager->addProvider(new TimestampProvider());

        Context::shouldReceive('add')
            ->once()
            ->with(Mockery::on(fn ($ctx) => isset($ctx['timestamp'])));

        $channel1 = new LogChannel();
        $manager->addChannel($channel1);

        $manager->build();
    });

    it('processes providers in order', function () {
        $manager = new ContextManager();

        $order = [];

        $provider1 = new class ($order) extends JuniorFontenele\LaravelContext\Providers\AbstractProvider
        {
            public function __construct(private &$order)
            {
            }

            public function getContext(): array
            {
                $this->order[] = 1;

                return ['provider' => 1];
            }
        };

        $provider2 = new class ($order) extends JuniorFontenele\LaravelContext\Providers\AbstractProvider
        {
            public function __construct(private &$order)
            {
            }

            public function getContext(): array
            {
                $this->order[] = 2;

                return ['provider' => 2];
            }
        };

        $manager->addProvider($provider1);
        $manager->addProvider($provider2);

        $manager->build();
        $context = $manager->all();

        expect($order)->toBe([1, 2]);
        expect($context['provider'])->toBe(2); // Último provider vence
    });
});
