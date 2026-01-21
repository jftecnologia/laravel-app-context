<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext;

use Illuminate\Support\Arr;
use JuniorFontenele\LaravelAppContext\Contracts\ContextChannel;
use JuniorFontenele\LaravelAppContext\Contracts\ContextProvider;

class ContextManager
{
    protected array $context = [];

    /** @var ContextProvider[] */
    protected array $providers = [];

    /** @var ContextChannel[] */
    protected array $channels = [];

    public function __construct(protected array $config)
    {
    }

    /*
    * Registers a provider
    */
    public function addProvider(ContextProvider $providers): self
    {
        $this->providers[] = $providers;

        return $this;
    }

    /*
    * Registers a channel
    */
    public function addChannel(ContextChannel $channel): self
    {
        $this->channels[] = $channel;

        return $this;
    }

    /*
    * Builds the context running the providers
    */
    public function resolveContext(): array
    {
        foreach ($this->providers as $provider) {
            if ($provider->shouldRun()) {
                $this->context = array_merge(
                    $this->context,
                    $provider->getContext()
                );
            }
        }

        $this->sendContextToChannels();

        return $this->context;
    }

    /**
     * Sends the resolved context to all registered channels
     */
    protected function sendContextToChannels(): void
    {
        foreach ($this->channels as $channel) {
            $channel->registerContext($this->context);
        }
    }

    /**
     * Returns the full context array
     */
    public function all(): array
    {
        $this->resolveContext();

        return $this->context;
    }

    /**
     * Returns a specific context value by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * Sets a specific context value by key
     */
    public function set(string $key, mixed $value): self
    {
        Arr::set($this->context, $key, $value);

        return $this;
    }

    /**
     * Clears the current context
     */
    public function clear(): self
    {
        $this->context = [];

        return $this;
    }
}
