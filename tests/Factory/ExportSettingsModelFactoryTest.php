<?php

declare(strict_types=1);

namespace CavernBay\TranslationBundle\Tests\Factory;

use CavernBay\TranslationBundle\Factory\ExportSettingsModelFactory;
use CavernBay\TranslationBundle\Model\ExportSettingsModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ExportSettingsModelFactoryTest extends TestCase
{
    public function testCreateFromConsoleInput(): void
    {
        $definition = new InputDefinition([
            new InputArgument('bundles'),
            new InputArgument('locale'),
            new InputArgument('locales'),
            new InputArgument('csv'),
            new InputOption('domains'),
            new InputOption('separator'),
            new InputOption('only-missing'),
            new InputOption('include-bom'),
        ]);
        $input = new ArgvInput([], $definition);
        $input->setArgument('bundles', 'app,TestBundle');
        $input->setArgument('locale', 'en');
        $input->setArgument('locales', 'fr,it');
        $input->setArgument('csv', 'test.csv');
        $input->setOption('domains', 'messages');
        $input->setOption('separator', ',');
        $input->setOption('only-missing', false);
        $factory = new ExportSettingsModelFactory();
        $model = $factory->createFromConsoleInput($input);

        static::assertEquals($this->getExpectedModel(), $model);
    }

    private function getExpectedModel(): ExportSettingsModel
    {
        $model = new ExportSettingsModel();
        $model->setFileName('test.csv');
        $model->setBundles(['app', 'TestBundle']);
        $model->setLocale('en');
        $model->setLocales(['fr', 'it']);
        $model->setDomains(['messages']);
        $model->setSeparator(',');
        $model->setOnlyMissing(false);
        $model->setIncludeUTF8Bom(false);

        return $model;
    }
}
