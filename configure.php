#!/usr/bin/env php
<?php

declare(strict_types = 1);

use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    writeln('Installing dependencies. Please wait...');
    run('composer install --no-interaction --prefer-dist');
}

if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    writeln('Could not install dependencies. Please run "composer install" and try again.');

    exit(1);
}

require __DIR__ . '/vendor/autoload.php';

function writeln(string $text = ''): void
{
    echo $text . PHP_EOL;
}

function run(string $command): string
{
    return trim((string) shell_exec($command));
}

function guessGitUser(): ?string
{
    $name = run('git config user.name');

    return $name ?: null;
}

function guessGitEmail(): ?string
{
    $email = run('git config user.email');

    return $email ?: null;
}

function guessVendorName(): ?string
{
    $name = guessGitUser();

    return $name ? Str::studly($name) : 'VendorName';
}

function guessPackageName(): ?string
{
    $name = basename(getcwd()) !== '.' ? basename(getcwd()) : 'PackageName';

    return Str::studly($name);
}

function listFilesToReplace(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i "author_name|author_email@authordomain.com|vendor_slug|package_slug|VendorName|PackageName|" --exclude-dir=vendor --exclude-dir=.git ./* ./.github/* | grep -v ' . basename(__FILE__)));
}

function replace_in_file(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

$author = text(
    label: 'Author Name',
    placeholder: 'Your full name',
    required: true,
    default: guessGitUser(),
);

$authorEmail = text(
    label: 'Author Email',
    placeholder: 'your.email@example.com',
    required: true,
    default: guessGitEmail(),
);

$vendorName = text(
    label: 'Vendor Name',
    placeholder: 'VendorName',
    required: true,
    default: guessVendorName(),
);

$packageName = text(
    label: 'Package Name',
    placeholder: 'PackageName',
    required: true,
    default: guessPackageName(),
);

$vendorSlug = text(
    label: 'Vendor Slug',
    placeholder: 'vendor-name',
    required: true,
    default: Str::kebab($vendorName),
);

$packageSlug = text(
    label: 'Package Slug',
    placeholder: 'package-name',
    required: true,
    default: Str::kebab($packageName),
);

$packageDescription = text(
    label: 'Package Description',
    placeholder: 'A brief description of your package',
    required: true,
    default: 'A Laravel package by ' . $author,
);

writeln('------');
writeln("Author             : $author");
writeln("Author Email       : $authorEmail");
writeln("Package Namespace  : $vendorName\\$packageName");
writeln("Package URL        : $vendorSlug/$packageSlug");
writeln("Package Description: $packageDescription");
writeln('------');
writeln('');
writeln('This script will replace the placeholders in the package skeleton with the provided values.');

confirm(
    label: 'Do you want to proceed?',
    default: true,
) || exit(writeln('Aborted. No changes were made.'));

$files = listFilesToReplace();

foreach ($files as $file) {
    replace_in_file($file, [
        'author_name' => $author,
        'author_email@authordomain.com' => $authorEmail,
        'vendor_slug' => $vendorSlug,
        'package_slug' => $packageSlug,
        'VendorName' => $vendorName,
        'PackageName' => $packageName,
        'package_description' => $packageDescription,
    ]);

    match (true) {
        str_contains($file, 'config/package_slug.php') => rename($file, str_replace('package_slug', $packageSlug, $file)),
        str_contains($file, 'src/PackageNameServiceProvider.php') => rename($file, str_replace('PackageName', $packageName, $file)),
        str_contains($file, 'src/Facades/PackageName.php') => rename($file, str_replace('PackageName', $packageName, $file)),
        str_contains($file, 'src/PackageName.php') => rename($file, str_replace('PackageName', $packageName, $file)),
        default => null,
    };
}

confirm(
    label: 'Install dependencies now (composer install)?',
    default: true,
) && run('composer update --no-interaction --prefer-dist');

confirm(
    label: 'Install Orchestra Testbench now (vendor/bin/testbench workbench:install)?',
    default: true,
) && system('vendor/bin/testbench workbench:install');

confirm(
    label: 'Do you want to remove the configure script?',
    default: true,
) && unlink(__FILE__);
