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

    /**
     * Flag to track if context has been resolved
     */
    protected bool $resolved = false;

    /**
     * Cache for providers that are cacheable
     * Key: provider class name, Value: cached context
     */
    protected array $providerCache = [];

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
    public function resolveContext(): self
    {
        $this->context = [];

        foreach ($this->providers as $provider) {
            if ($provider->shouldRun()) {
                $providerClass = get_class($provider);

                // Use cached context if provider is cacheable and cache exists
                if ($provider->isCacheable() && isset($this->providerCache[$providerClass])) {
                    $providerContext = $this->providerCache[$providerClass];
                } else {
                    // Get fresh context and cache it if provider is cacheable
                    $providerContext = $provider->getContext();

                    if ($provider->isCacheable()) {
                        $this->providerCache[$providerClass] = $providerContext;
                    }
                }

                $this->context = array_merge($this->context, $providerContext);
            }
        }

        $this->sendContextToChannels();
        $this->resolved = true;

        return $this;
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
        if (! $this->resolved) {
            $this->resolveContext();
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
     * Sets a specific context value by key
     */
    public function set(string $key, mixed $value): self
    {
        Arr::set($this->context, $key, $value);

        return $this;
    }

    /**
     * Forces context recalculation
     */
    public function refresh(): self
    {
        $this->resolved = false;
        $this->providerCache = [];

        return $this->resolveContext();
    }

    /**
     * Clears cache for a specific provider
     *
     * @param string $providerClass Fully qualified class name of the provider
     */
    public function clearProviderCache(string $providerClass): self
    {
        unset($this->providerCache[$providerClass]);
        $this->resolved = false;

        return $this;
    }

    /**
     * Clears the current context
     */
    public function clear(): self
    {
        $this->context = [];
        $this->resolved = false;
        $this->providerCache = [];

        return $this;
    }
}
