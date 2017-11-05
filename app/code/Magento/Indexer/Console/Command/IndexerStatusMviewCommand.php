<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Mview\View;

/**
 * Command for displaying status of mview indexers.
 */
class IndexerStatusMviewCommand extends Command
{
    /** @var \Magento\Framework\Mview\View\CollectionInterface $mviewIndexersCollection */
    private $mviewIndexersCollection;

    public function __construct(
        \Magento\Framework\Mview\View\CollectionInterface $collection
    ) {
        $this->mviewIndexersCollection = $collection;
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

            /** @var \Magento\Framework\Mview\View $indexer */
            foreach ($this->mviewIndexersCollection as $indexer) {
                $state = $indexer->getState();
                $changelog = $indexer->getChangelog();

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
                    $indexer->getData('view_id'),
                    $state->getData('mode'),
                    $state->getData('status'),
                    $state->getData('updated'),
                    $state->getData('version_id'),
                    $pendingString,
                ];
            }

            usort($rows, function ($a, $b) {
                return strcmp($a[0], $b[0]);
            });

            $table->addRows($rows);
            $table->render($output);

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
