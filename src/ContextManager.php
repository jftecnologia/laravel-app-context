<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelContext;

use Illuminate\Support\Arr;
use JuniorFontenele\LaravelContext\Contracts\ContextChannel;
use JuniorFontenele\LaravelContext\Contracts\ContextProvider;

final class ContextManager
{
    protected array $context = [];

    /** @var ContextProvider[] */
    protected array $providers = [];

    /** @var ContextChannel[] */
    protected array $channels = [];

    protected bool $built = false;

    /** @var array<string, array> */
    protected array $providerCache = [];

    /**
     * Registers a provider
     */
    public function addProvider(ContextProvider $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Registers a channel
     */
    public function addChannel(ContextChannel $channel): self
    {
        $this->channels[] = $channel;

        return $this;
    }

    /**
     * Builds the context running the providers
     */
    public function build(): self
    {
        $this->context = [];

        foreach ($this->providers as $provider) {
            if ($provider->shouldRun()) {
                $providerClass = get_class($provider);

                if ($provider->isCacheable() && isset($this->providerCache[$providerClass])) {
                    $providerContext = $this->providerCache[$providerClass];
                } else {
                    $providerContext = $provider->getContext();

                    if ($provider->isCacheable()) {
                        $this->providerCache[$providerClass] = $providerContext;
                    }
                }

                $this->context = array_merge($this->context, $providerContext);
            }
        }

        $this->sendContextToChannels();

        $this->built = true;

        return $this;
    }

    /**
     * Register the context to all registered channels
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
        if (! $this->built) {
            $this->build();
        }

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
     * Checks if a context key exists
     */
    public function has(string $key): bool
    {
        return Arr::has($this->all(), $key);
    }

    /**
     * Sets a specific context value by key in runtime
     */
    public function set(string $key, mixed $value): self
    {
        Arr::set($this->context, $key, $value);

        return $this;
    }

    /**
     * Rebuilds the context clearing the cache
     */
    public function rebuild(): self
    {
        $this->clear();

        return $this->build();
    }

    /**
     * Clears context cache for a specific provider
     *
     * @param string $providerClass Fully qualified class name of the provider
     */
    public function clearProviderCache(string $providerClass): self
    {
        unset($this->providerCache[$providerClass]);

        return $this;
    }

    /**
     * Clears the current context and cache
     */
    public function clear(): self
    {
        $this->context = [];
        $this->providerCache = [];
        $this->built = true;

        return $this;
    }

    /**
     * Resets the context sending an empty context to channels
     */
    public function reset(): self
    {
        $this->clear();

        $this->sendContextToChannels();

        return $this;
    }
}
