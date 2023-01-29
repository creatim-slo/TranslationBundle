<?php

declare(strict_types=1);

namespace CavernBay\TranslationBundle\Model;

class ImportSettingsModel
{
    private array $bundlesNames;
    private array $domains;
    private array $locales;
    private string $csv;
    private string $separator;
    private bool $overwriteExisting = false;

    public function getBundlesNames(): array
    {
        return $this->bundlesNames;
    }

    public function setBundlesNames(array $bundlesNames): void
    {
        $this->bundlesNames = $bundlesNames;
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function setDomains(array $domains): void
    {
        $this->domains = $domains;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function getCsv(): string
    {
        return $this->csv;
    }

    public function setCsv(string $csv): void
    {
        $this->csv = $csv;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    public function isOverwriteExisting(): bool
    {
        return $this->overwriteExisting;
    }

    public function setOverwriteExisting(bool $overwriteExisting): void
    {
        $this->overwriteExisting = $overwriteExisting;
    }
}
