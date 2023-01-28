<?php

declare(strict_types=1);

namespace CavernBay\TranslationBundle\Model;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ExportSettingsModel
{
    /** @var string[]|BundleInterface[] */
    private array $bundles;
    private string $locale;
    /** @var string[] */
    private array $locales;
    /** @var string[] */
    private array $domains;
    private string $separator;
    private bool $onlyMissing;
    private bool $includeUTF8Bom;
    private string $fileName;

    public function getBundles(): array
    {
        return $this->bundles;
    }

    public function setBundles(array $bundles): void
    {
        $this->bundles = $bundles;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function setDomains(array $domains): void
    {
        $this->domains = $domains;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    public function isOnlyMissing(): bool
    {
        return $this->onlyMissing;
    }

    public function setOnlyMissing(bool $onlyMissing): void
    {
        $this->onlyMissing = $onlyMissing;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function isIncludeUTF8Bom(): bool
    {
        return $this->includeUTF8Bom;
    }

    public function setIncludeUTF8Bom(bool $includeUTF8Bom): void
    {
        $this->includeUTF8Bom = $includeUTF8Bom;
    }
}
