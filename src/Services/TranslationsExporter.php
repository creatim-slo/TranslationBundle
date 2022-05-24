<?php

declare(strict_types=1);

namespace Kilik\TranslationBundle\Services;

use Kilik\TranslationBundle\Model\ExportSettingsModel;
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


    /**
     * Makes sure translation files with multi line strings result in correct csv files.
     *
     * @param string $str
     *
     * @return string
     */
    protected function fixMultiLine($str)
    {
        $str = str_replace(PHP_EOL, "\\n", $str);
        if (substr($str, -2) === "\\n") {
            $str = substr($str, 0, -2);//Not doing this results in \n at the end of some strings after import.
        }

        return $str;
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
        $locale = $exportSettingsModel->getLocale();
        $locales = $exportSettingsModel->getLocales();

        $separator = $exportSettingsModel->getSeparator();

        // and export data as CSV (tab separated values)
        $columns = ['Bundle', 'Domain', 'Key', $locale];
        foreach ($locales as $localeColumn) {
            $columns[] = $localeColumn;
        }

        $buffer = implode($separator, $columns).PHP_EOL;

        foreach ($this->loadTranslationService->getTranslations() as $bundleName => $domains) {
            foreach ($domains as $domain => $translations) {
                foreach ($translations as $trKey => $trLocales) {
                    $missing = false;

                    $data = [$bundleName, $domain, $trKey];
                    if (isset($trLocales[$locale])) {
                        $data[] = $this->fixMultiLine($trLocales[$locale]);
                    } else {
                        $data[] = '';
                        $missing = true;
                    }

                    foreach ($locales as $trLocale) {
                        if (isset($trLocales[$trLocale])) {
                            $data[] = $this->fixMultiLine($trLocales[$trLocale]);
                        } else {
                            $data[] = '';
                            $missing = true;
                        }
                    }

                    if (!$exportSettingsModel->isOnlyMissing() || $missing) {
                        $buffer .= implode($separator, $data).PHP_EOL;
                    }
                }
            }
        }
        file_put_contents($exportSettingsModel->getFileName(), $buffer);
    }
}
