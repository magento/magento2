<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for recreating MySQL Triggers.
 */
class RebuildTriggersCommand extends AbstractIndexerManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:rebuild-triggers')
            ->setDescription('Recreate MySQL Triggers')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);
        
        foreach ($indexers as $indexer) {
            $isScheduled = (bool)$indexer->isScheduled();
            
            // 'turn off and on again'
            $indexer->setScheduled(!$isScheduled);
            $indexer->setScheduled($isScheduled);
            
            $status = $isScheduled ? 'Update by Schedule' : 'Update on Save';
            $output->writeln(sprintf('Triggers rebuilt for %-30s: %s ', $indexer->getTitle(), $status));
        }
    }
}
