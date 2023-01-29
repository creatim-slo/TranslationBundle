<?php

namespace CavernBay\TranslationBundle\Command;

use CavernBay\TranslationBundle\Factory\ImportSettingsModelFactory;
use CavernBay\TranslationBundle\Services\TranslationsImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'cavernbay:translation:import',
    description: 'Import translations from CSV files to project bundles',
    aliases: ['kilik:translation:import', 'cavern-bay:translation:import', 'cb:translation:import'],
    hidden: false,
)]
class ImportCommand extends Command
{
    private ImportSettingsModelFactory $importSettingsModelFactory;
    private TranslationsImporter $translationsImporter;

    #[Required]
    public function setImportSettingsModelFactory(ImportSettingsModelFactory $importSettingsModelFactory): void
    {
        $this->importSettingsModelFactory = $importSettingsModelFactory;
    }

    #[Required]
    public function setTranslationsImporter(TranslationsImporter $translationsImporter): void
    {
        $this->translationsImporter = $translationsImporter;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->addArgument('locales', InputArgument::REQUIRED, 'Locales to import from CSV file to bundles')
            ->addArgument('csv', InputArgument::REQUIRED, 'Output CSV filename')
            ->addOption('domains', null, InputOption::VALUE_OPTIONAL, 'Domains', 'all')
            ->addOption('bundles', null, InputOption::VALUE_OPTIONAL, 'Limit to bundles', 'all')
            ->addOption('overwrite-existing', 'o', InputOption::VALUE_NONE, 'Overwrite the existing translations, instead of merging them')
            ->addOption('separator', 'sep', InputOption::VALUE_REQUIRED, 'The character used as separator', "\t");
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importSettingsModel = $this->importSettingsModelFactory->createFromConsoleInput($input);
        $importedFiles = $this->translationsImporter->import($importSettingsModel);

        foreach ($importedFiles as $importedFile) {
            $output->writeln(sprintf('<info>File %s updated</info>', $importedFile));
        }

        return Command::SUCCESS;
    }
}
