<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerReindexCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerReindexCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:reindex')
            ->setDescription(
                'Reindexes Data'
            )->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->getIndexers($input);
        foreach ($indexers as $indexer) {
            try {
                $startTime = microtime(true);
                $indexer->reindexAll();
                $resultTime = microtime(true) - $startTime;
                $output->writeln( $indexer->getTitle() . ' index has been rebuilt successfully in '
                    . gmdate('H:i:s', $resultTime) );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $output->writeln($e->getMessage());
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                $output->writeln($e);
            }
        }
    }
}
