<?php

declare(strict_types=1);

namespace Kilik\TranslationBundle\Services;

use Kilik\TranslationBundle\Model\ExportSettingsModel;
use League\Csv\Writer;
use Symfony\Component\HttpKernel\KernelInterface;

class TranslationsExporter
{
    public const APP_BUNDLE_NAME = 'app';

    private LoadTranslationService $loadTranslationService;
    private ReporterService $reporterService;
    private KernelInterface $kernel;

    public function __construct(
        LoadTranslationService $loadTranslationService,
        ReporterService $reporterService,
        KernelInterface $kernel
    ) {
        $this->loadTranslationService = $loadTranslationService;
        $this->reporterService   = $reporterService;
        $this->kernel = $kernel;
    }

    public function export(ExportSettingsModel $exportSettingsModel): void
    {
        $bundlesNames = $exportSettingsModel->getBundles();

        $this->loadBundlesTranslations($bundlesNames, $exportSettingsModel);
        $this->exportTranslationsToFile($exportSettingsModel);
    }

    protected function loadBundlesTranslations(array $bundles, ExportSettingsModel $exportSettingsModel): void
    {
        foreach ($bundles as $bundle) {
            // fix symfony 4 applications (use magic bundle name "app")
            if (static::APP_BUNDLE_NAME === $bundle) {
                $this->loadAppTranslations($exportSettingsModel);
                continue;
            }

            $this->loadBundleTranslations($bundle, $exportSettingsModel);
        }
    }

    protected function loadBundleTranslations($bundleName, ExportSettingsModel $exportSettingsModel): void
    {
        $bundle = $this->kernel->getBundle($bundleName);

        if (method_exists($bundle, 'getParent') && null !== $bundle->getParent()) {
            $bundles = $this->kernel->getBundle($bundle->getParent(), false);
            $bundle = $bundles[1];
            $this->reporterService->report(sprintf(
                'Using: %s as bundle to lookup translations files for.',
                $bundle->getName()
            ));
        }

        // locales to export
        $this->loadTranslationService->loadBundleTranslationFiles(
            $bundle,
            $exportSettingsModel->getLocales(),
            $exportSettingsModel->getDomains()
        );
        // locale reference
        $this->loadTranslationService->loadBundleTranslationFiles(
            $bundle,
            [$exportSettingsModel->getLocale()],
            $exportSettingsModel->getDomains()
        );
    }

    protected function loadAppTranslations(ExportSettingsModel $exportSettingsModel): void
    {
        $this->loadTranslationService->loadAppTranslationFiles(
            $exportSettingsModel->getLocales(),
            $exportSettingsModel->getDomains()
        );
        // locale reference
        $this->loadTranslationService->loadAppTranslationFiles(
            [$exportSettingsModel->getLocale()],
            $exportSettingsModel->getDomains()
        );
    }

    protected function exportTranslationsToFile(ExportSettingsModel $exportSettingsModel): void
    {
        $locales = [$exportSettingsModel->getLocale(), ...$exportSettingsModel->getLocales()];

        $writer = Writer::createFromPath($exportSettingsModel->getFileName(), 'w+');
        $writer->setDelimiter($exportSettingsModel->getSeparator());

        $columns = ['Bundle', 'Domain', 'Key', ...$locales];

        $writer->insertOne($columns);

        foreach ($this->loadTranslationService->getTranslations() as $bundleName => $domains) {
            foreach ($domains as $domain => $translations) {
                foreach ($translations as $trKey => $trLocales) {
                    if ($this->shouldExportRow($trLocales, $locales, $exportSettingsModel->isOnlyMissing())) {
                        $translatedLocales = array_map(fn ($locale) => $trLocales[$locale] ?? '', $locales);
                        $row = [$bundleName, $domain, $trKey, ...$translatedLocales];
                        $writer->insertOne($row);
                    }
                }
            }
        }
    }

    protected function shouldExportRow(array $translations, array $locales, bool $isMissingOnly): bool
    {
        if (!$isMissingOnly) {
            return true;
        }

        // checks if at least one entry from $locales is missing in $translations,
        // returns true in that case, else otherwise
        return array_reduce($locales, fn ($prev, $locale) => $prev || !isset($translations[$locale]), false);
    }
}
