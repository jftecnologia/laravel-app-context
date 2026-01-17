<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelAppContext\Services;

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

    protected bool $built = false;

    public function __construct(protected array $config)
    {
    }

    /*
    * Registers a provider
    */
    public function addProvider(ContextProvider $providers): self
    {
        $this->providers[] = $providers;

        $this->built = false;

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
    public function build(): array
    {
        if ($this->built) {
            return $this->context;
        }

        foreach ($this->providers as $provider) {
            if ($provider->shouldRun()) {
                $this->context = array_merge(
                    $this->context,
                    $provider->getContext()
                );
            }
        }

        $this->built = true;

        return $this->context;
    }

    /**
     * Sends the built context to all enabled channels
     */
    public function buildChannelsContext(): void
    {
        foreach ($this->channels as $channel) {
            if ($channel->isEnabled()) {
                $channel->send($this->resolveContext($this->context));
            }
        }
    }

    /**
     * Resolves any callable values in the context array
     */
    protected function resolveContext(array $context): array
    {
        $resolvedContext = [];

        foreach ($context as $key => $value) {
            if (is_callable($value)) {
                $resolvedContext[$key] = $value();
            } else {
                $resolvedContext[$key] = $value;
            }
        }

        return $resolvedContext;
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
        $this->built = false;

        return $this;
    }
}
