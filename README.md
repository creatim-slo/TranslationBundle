CavernBay Translation Bundle
========================

> This is a reworked fork of kilik/translation-bundle, adapted to symfony 5 and symfony 6 and php 8.1+. If you need translations for older versions, you can use kilik/translation-bundle instead

CBTB (CavernBay Translation Bundle) is a tool to be used with Symfony Translator. It tries to simplify the exchanges with the (human) translators.

From the command line you can export translations (filtering with bundles names, domains, and locales) to CSV (Tab separators).

Your translator (colleague, service provider, etc...) can open CSV files with specific translator tools (or with Office Software).

Then, you can import updated translations to your project.

The translations made in vendors are also supported (useful when you have to work on big applications with a lot of bundles).

Concepts:

- your project is fully translated in a locale (locale reference, fallback)
- it aims to simplify the process to translate missing translations with non-team people

Add this bundle to your application
===================================

Update composer.json:

    "repositories": [
        { "type": "git", "url": "git@github.com:creatim-slo/TranslationBundle.git" }
    ],

    "require": {
        "cavernbay/translation-bundle": "dev-master"
    }

Run:

    composer update cavernbay/translation-bundle

Then, add this line to your bundles.php:

        $bundles = [
            // ...
            new CavernBay\TranslationBundle\CavernBayTranslationBundle(),
            // ...
        ];

Export translations examples
============================

### export App bundle only
    php bin/console cavernbay:translation:export sl en app path-to/app.csv

### export all bundles
    php bin/console cavernbay:translation:export sl en all path-to/app.csv

### export SyliusShopBundle bundle only
    php bin/console cavernbay:translation:export sl en SyliusShopBundle path-to/sylius_shop.csv

Import translations
===================

    php bin/console cavernbay:translation:import sl,en path-to/app.csv


