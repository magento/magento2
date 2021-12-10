<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\CollectionInterface;
use Magento\Setup\Fixtures\FixtureModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command generates fixtures for performance tests
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateFixturesCommand extends Command
{
    public const PROFILE_ARGUMENT = 'profile';

    public const SKIP_REINDEX_OPTION = 'skip-reindex';

    /**
     * @var FixtureModel
     */
    private $fixtureModel;

    /**
     * @param FixtureModel $fixtureModel
     */
    public function __construct(FixtureModel $fixtureModel)
    {
        $this->fixtureModel = $fixtureModel;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('setup:performance:generate-fixtures')
            ->setDescription('Generates fixtures')
            ->setDefinition([
                new InputArgument(
                    self::PROFILE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Path to profile configuration file'
                ),
                new InputOption(
                    self::SKIP_REINDEX_OPTION,
                    's',
                    InputOption::VALUE_NONE,
                    'Skip reindex'
                )
            ]);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $totalStartTime = microtime(true);

            $fixtureModel = $this->fixtureModel;
            $fixtureModel->loadConfig($input->getArgument(self::PROFILE_ARGUMENT));
            $fixtureModel->initObjectManager();
            $fixtureModel->loadFixtures();

            $output->writeln('<info>Generating profile with following params:</info>');

            foreach ($fixtureModel->getFixtures() as $fixture) {
                $fixture->printInfo($output);
            }

            /** @var \Magento\Setup\Fixtures\ConfigsApplyFixture $configFixture */
            $configFixture = $fixtureModel
                ->getFixtureByName(\Magento\Setup\Fixtures\ConfigsApplyFixture::class);
            $configFixture && $this->executeFixture($configFixture, $output);

            /** @var $config \Magento\Indexer\Model\Config */
            $config = $fixtureModel->getObjectManager()->get(\Magento\Indexer\Model\Config::class);
            $indexerListIds = $config->getIndexers();
            /** @var $indexerRegistry \Magento\Framework\Indexer\IndexerRegistry */
            $indexerRegistry = $fixtureModel->getObjectManager()
                ->create(\Magento\Framework\Indexer\IndexerRegistry::class);

            $indexersState = [];
            foreach ($indexerListIds as $indexerId) {
                $indexer = $indexerRegistry->get($indexerId['indexer_id']);
                $indexersState[$indexerId['indexer_id']] = $indexer->isScheduled();
                $indexer->setScheduled(true);
            }

            foreach ($fixtureModel->getFixtures() as $fixture) {
                $this->executeFixture($fixture, $output);
            }

            $this->clearChangelog();

            foreach ($indexerListIds as $indexerId) {
                /** @var $indexer \Magento\Indexer\Model\Indexer */
                $indexer = $indexerRegistry->get($indexerId['indexer_id']);
                $indexer->setScheduled($indexersState[$indexerId['indexer_id']]);
            }

            /** @var \Magento\Setup\Fixtures\IndexersStatesApplyFixture $indexerFixture */
            $indexerFixture = $fixtureModel
                ->getFixtureByName(\Magento\Setup\Fixtures\IndexersStatesApplyFixture::class);
            $indexerFixture && $this->executeFixture($indexerFixture, $output);

            if (!$input->getOption(self::SKIP_REINDEX_OPTION)) {
                $fixtureModel->reindex($output);
            }

            $totalEndTime = microtime(true);
            $totalResultTime = (int) ($totalEndTime - $totalStartTime);
            $output->writeln('<info>Total execution time: ' . gmdate('H:i:s', $totalResultTime) . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Clear changelog after generation
     *
     * @return void
     */
    private function clearChangelog()
    {
        $viewConfig = $this->fixtureModel->getObjectManager()->create(CollectionInterface::class);

        /* @var ResourceConnection $resource */
        $resource = $this->fixtureModel->getObjectManager()->get(ResourceConnection::class);

        foreach ($viewConfig as $view) {
            /* @var \Magento\Framework\Mview\ViewInterface $view */
            $changeLogTableName = $resource->getTableName($view->getChangelog()->getName());
            if ($resource->getConnection()->isTableExists($changeLogTableName)) {
                $resource->getConnection()->truncateTable($changeLogTableName);
            }
        }
    }

    /**
     * Executes fixture and output the execution time.
     *
     * @param \Magento\Setup\Fixtures\Fixture $fixture
     * @param OutputInterface $output
     */
    private function executeFixture(\Magento\Setup\Fixtures\Fixture $fixture, OutputInterface $output)
    {
        $output->write('<info>' . $fixture->getActionTitle() . '... </info>');
        $startTime = microtime(true);
        $fixture->execute($output);
        $endTime = microtime(true);
        $resultTime = (int) ($endTime - $startTime);
        $output->writeln('<info> done in ' . gmdate('H:i:s', $resultTime) . '</info>');
    }
}
