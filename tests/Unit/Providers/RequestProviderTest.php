<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\Providers\RequestProvider;

describe('RequestProvider', function () {
    it('extends AbstractProvider', function () {
        $provider = new RequestProvider();

        expect($provider)->toBeInstanceOf(JuniorFontenele\LaravelContext\Providers\AbstractProvider::class);
    });
});
