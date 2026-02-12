<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelContext\Providers\UserProvider;

describe('UserProvider', function () {
    it('extends AbstractProvider', function () {
        $provider = new UserProvider();

        expect($provider)->toBeInstanceOf(JuniorFontenele\LaravelContext\Providers\AbstractProvider::class);
    });
});
