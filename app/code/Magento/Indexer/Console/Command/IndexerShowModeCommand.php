<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying current index mode for indexers.
 * @since 2.0.0
 */
class IndexerShowModeCommand extends AbstractIndexerManageCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('indexer:show-mode')
            ->setDescription('Shows Index Mode')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);
        foreach ($indexers as $indexer) {
            $status = $indexer->isScheduled() ? 'Update by Schedule' : 'Update on Save';
            $output->writeln(sprintf('%-50s ', $indexer->getTitle() . ':') . $status);
        }
    }
}
