<?php

declare(strict_types = 1);

use Illuminate\Support\Carbon;
use JuniorFontenele\LaravelContext\Providers\TimestampProvider;

describe('TimestampProvider', function () {
    it('returns timestamp in ISO 8601 format', function () {
        $provider = new TimestampProvider();
        $context = $provider->getContext();

        expect($context)->toHaveKey('timestamp');
        expect($context['timestamp'])->toBeString();
        expect($context['timestamp'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/');
    });

    it('generates different timestamps over time', function () {
        $provider = new TimestampProvider();

        $now = now();
        Carbon::setTestNow($now);

        $context1 = $provider->getContext();

        Carbon::setTestNow($now->addSeconds(5));

        $context2 = $provider->getContext();

        expect($context1['timestamp'])->not()->toBe($context2['timestamp']);

        Carbon::setTestNow(); // Clear the test time
    });

    it('should run by default', function () {
        $provider = new TimestampProvider();

        expect($provider->shouldRun())->toBeTrue();
    });
});
