<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Console\Cli;

/**
 * Command for displaying status of mview indexers.
 */
class IndexerStatusMviewCommand extends Command
{
    /** @var \Magento\Framework\Mview\View\CollectionInterface $mviewCollection */
    private $mviewCollection;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->mviewCollection = $collectionFactory->create();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:status:mview')
            ->setDescription('Shows status of Mview Indexers and their queue status');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $table = $this->getHelperSet()->get('table');
            $table->setHeaders(['ID', 'Mode', 'Status', 'Updated', 'Version ID', 'Backlog']);

            $rows = [];

            /** @var \Magento\Framework\Mview\View $view */
            foreach ($this->mviewCollection as $view) {
                $state = $view->getState();
                $changelog = $view->getChangelog();

                try {
                    $currentVersionId = $changelog->getVersion();
                } catch (View\ChangelogTableNotExistsException $e) {
                    continue;
                }

                $pendingCount = count($changelog->getList($state->getVersionId(), $currentVersionId));

                $pendingString = "<error>$pendingCount</error>";
                if ($pendingCount <= 0) {
                    $pendingString = "<info>$pendingCount</info>";
                }

                $rows[] = [
                    $view->getId(),
                    $state->getMode(),
                    $state->getStatus(),
                    $state->getUpdated(),
                    $state->getVersionId(),
                    $pendingString,
                ];
            }

            usort($rows, function ($comp1, $comp2) {
                return strcmp($comp1[0], $comp2[0]);
            });

            $table->addRows($rows);
            $table->render($output);

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return Cli::RETURN_FAILURE;
        }
    }
}
