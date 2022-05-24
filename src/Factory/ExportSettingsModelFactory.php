<?php

declare(strict_types=1);

namespace Kilik\TranslationBundle\Factory;

use Kilik\TranslationBundle\Model\ExportSettingsModel;
use Symfony\Component\Console\Input\InputInterface;

class ExportSettingsModelFactory
{
    public function createFromConsoleInput(InputInterface $input): ExportSettingsModel
    {
        $model = new ExportSettingsModel();
        $model->setBundles(explode(',', $input->getArgument('bundles')));
        $model->setLocale($input->getArgument('locale'));
        $model->setLocales(explode(',', $input->getArgument('locales')));
        $model->setDomains(explode(',', $input->getOption('domains')));
        $model->setSeparator($input->getOption('separator'));
        $model->setOnlyMissing((bool) $input->getOption('only-missing'));
        $model->setFileName($input->getArgument('csv'));

        return $model;
    }
}
