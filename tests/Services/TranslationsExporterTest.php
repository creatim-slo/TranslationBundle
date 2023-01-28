<?php

declare(strict_types=1);

namespace CavernBay\TranslationBundle\Tests\Services;

use CavernBay\TranslationBundle\Model\ExportSettingsModel;
use CavernBay\TranslationBundle\Services\LoadTranslationService;
use CavernBay\TranslationBundle\Services\ReporterService;
use CavernBay\TranslationBundle\Services\TranslationsExporter;
use League\Csv\Writer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TranslationsExporterTest extends TestCase
{
    /**
     * @dataProvider getTestExportWithEmptyTranslationsCases
     */
    public function testExportWithEmptyTranslations(
        string $fileName,
        array $fixture,
        string $expectedContent
    ): void {
        $loadTranslationService = $this->createConfiguredMock(
            LoadTranslationService::class,
            [
                'getTranslations' => $fixture['translations'],
            ]
        );
        $reporterService = $this->createMock(ReporterService::class);
        $kernel = $this->createConfiguredMock(KernelInterface::class, [
            'getBundle' => $this->createMock(BundleInterface::class),
        ]);

        $exporter = new TranslationsExporter($loadTranslationService, $reporterService, $kernel);

        $exporterSettingsModel = new ExportSettingsModel();
        $exporterSettingsModel->setFileName('test1.csv');
        $exporterSettingsModel->setSeparator(',');
        $exporterSettingsModel->setBundles(['app', 'TestBundle']);
        $exporterSettingsModel->setLocale('en');
        $exporterSettingsModel->setLocales(['de', 'fr']);
        $exporterSettingsModel->setDomains($fixture['domains'] ?? []);
        $exporterSettingsModel->setOnlyMissing($fixture['onlyMissing'] ?? false);
        $exporterSettingsModel->setIncludeUTF8Bom($fixture['includeBom'] ?? false);

        $exporter->export($exporterSettingsModel);

        static::assertFileExists($fileName);
        static::assertStringEqualsFile($fileName, $expectedContent);
    }

    public function getTestExportWithEmptyTranslationsCases(): array
    {
        $cases = [];

        $cases['with_empty_translations'] = [
            'fileName' => 'test1.csv',
            'fixture' => [
                'translations' => [],
                'domains' => [],
            ],

            'expectedContent' => "Bundle,Domain,Key,en,de,fr\n",
        ];

        $cases['with_app_and_bundle_translations'] = [
            'fileName' => 'test1.csv',
            'fixture' => [
                'translations' => [
                    'app' => [
                        'messages' => [
                            'app.hello' => [
                                'en' => 'Hello',
                                'fr' => 'Salut',
                                'de' => 'Hallo',
                                'it' => 'Ciao',
                            ]
                        ],
                    ],
                    'TestBundle' => [
                        'messages' => [
                            'test_bundle.it_works' => [
                                'en' => 'It works',
                                'fr' => 'Ça marche',
                                'de' => 'Es klappt',
                                'it' => 'Funziona',
                            ]
                        ],
                    ],
                ],
                'domains' => [],
            ],
            'expectedContent' => "Bundle,Domain,Key,en,de,fr\n".
                "app,messages,app.hello,Hello,Hallo,Salut\n".
                "TestBundle,messages,test_bundle.it_works,\"It works\",\"Es klappt\",\"Ça marche\"\n",
        ];

        $cases['only_missing_translations'] = [
            'fileName' => 'test1.csv',
            'fixture' => [
                'translations' => [
                    'app' => [
                        'messages' => [
                            'app.hello' => [
                                'en' => 'Hello',
                                'fr' => 'Salut',
                                'it' => 'Ciao',
                            ]
                        ],
                    ],
                    'TestBundle' => [
                        'messages' => [
                            'test_bundle.it_works' => [
                                'en' => 'It works',
                                'fr' => 'Ça marche',
                                'de' => 'Es klappt',
                                'it' => 'Funziona',
                            ]
                        ],
                    ],
                ],
                'domains' => [],
                'onlyMissing' => true,
            ],
            'expectedContent' => "Bundle,Domain,Key,en,de,fr\n".
                "app,messages,app.hello,Hello,,Salut\n",
        ];

        $cases['translations_with_bom'] = [
            'fileName' => 'test1.csv',
            'fixture' => [
                'translations' => [
                    'app' => [
                        'messages' => [
                            'app.hello' => [
                                'en' => 'Hello',
                                'fr' => 'Salut',
                                'it' => 'Ciao',
                            ]
                        ],
                    ],
                ],
                'domains' => [],
                'includeBom' => true,
            ],
            'expectedContent' => Writer::BOM_UTF8."Bundle,Domain,Key,en,de,fr\n".
                "app,messages,app.hello,Hello,,Salut\n",
        ];

        return $cases;
    }
}
