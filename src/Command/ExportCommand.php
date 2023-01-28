<?php
/**
 * This class is inspired from https://github.com/lexik/LexikTranslationBundle.
 */

namespace CavernBay\TranslationBundle\Command;

use CavernBay\TranslationBundle\Factory\ExportSettingsModelFactory;
use CavernBay\TranslationBundle\Services\ReporterService;
use CavernBay\TranslationBundle\Services\TranslationsExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Class ExportCommand.
 */
class ExportCommand extends Command
{

    private TranslationsExporter $translationsExporter;
    private ExportSettingsModelFactory $exportSettingsModelFactory;
    private ReporterService $reporterService;

    /**
     * @required
     */
    #[Required]
    public function setTranslationsExporter(TranslationsExporter $translationsExporter): void
    {
        $this->translationsExporter = $translationsExporter;
    }

    /**
     * @required
     */
    #[Required]
    public function setExportSettingsModelFactory(ExportSettingsModelFactory $exportSettingsModelFactory): void
    {
        $this->exportSettingsModelFactory = $exportSettingsModelFactory;
    }

    /**
     * @required
     */
    #[Required]
    public function setReporterService(ReporterService $reporterService): void
    {
        $this->reporterService = $reporterService;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('cavernbay:translation:export')
            ->setAliases(['kilik:translation:export', 'cavern-bay:translation:export', 'cb:translation:export'])
            ->setDescription('Export translations from project bundles to CSV file')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale used as reference in application')
            ->addArgument('locales', InputArgument::REQUIRED, 'Locales to export missing translations')
            ->addArgument('bundles', InputArgument::REQUIRED, 'Bundles scope (app for symfony4 core application)')
            ->addArgument('csv', InputArgument::REQUIRED, 'Output CSV filename')
            ->addOption('domains', null, InputOption::VALUE_OPTIONAL, 'Domains', 'all')
            ->addOption('only-missing', null, InputOption::VALUE_NONE, 'Export only missing translations')
            ->addOption('include-bom', null, InputOption::VALUE_NONE, 'Includes UTF-8 BOM header (compatibility with Excel)')
            ->addOption('separator', 'sep', InputOption::VALUE_REQUIRED, 'The character used as separator', "\t");
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model = $this->exportSettingsModelFactory->createFromConsoleInput($input);

        $this->reporterService->addReporter(
            fn($data) => $output->writeln(sprintf('<info>%s</info>', $data))
        );
        $this->translationsExporter->export($model);

        $output->writeln('<info>Saving translations to : '.$model->getFileName().' (CSV tab separated value).</info>');

        return Command::SUCCESS;
    }
}
