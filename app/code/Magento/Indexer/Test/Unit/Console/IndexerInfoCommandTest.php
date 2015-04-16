<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerInfoCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerInfoCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:info')
            ->setDescription(
                'Shows allowed Indexers'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexers = $this->parseIndexerString('all');
        foreach ($indexers as $indexer) {
            $output->writeln(sprintf('%-40s %s', $indexer->getId(), $indexer->getTitle()));
        }
    }

    public function getOptionsList()
    {
        return [];
    }
}
