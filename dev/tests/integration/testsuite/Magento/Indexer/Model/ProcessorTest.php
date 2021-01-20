<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface as IndexerConfig;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Indexer\Model\Processor
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Indexer\Config\Converter
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->processor = Bootstrap::getObjectManager()->create(Processor::class);
    }

    /**
     * @return void
     */
    public function testReindexAllInvalid()
    {
        $indexerConfig = Bootstrap::getObjectManager()->create(IndexerConfig::class);
        $indexerFactory = Bootstrap::getObjectManager()->create(IndexerInterfaceFactory::class);
        $indexerIds = array_keys($indexerConfig->getIndexers());

        foreach ($indexerIds as $indexerId) {
            $indexer = $indexerFactory->create()->load($indexerId);
            $indexer->invalidate();
        }

        $this->processor->reindexAllInvalid();

        $notValidIndexers = [];
        foreach ($indexerIds as $indexerId) {
            $indexer = $indexerFactory->create()->load($indexerId);
            if ($indexer->isValid()) {
                continue;
            }

            $notValidIndexers[] = $indexer->getId();
        }
        $this->assertEmpty(
            $notValidIndexers,
            'Following indexers are not valid: ' . implode(', ', $notValidIndexers)
        );
    }

    /**
     * @return void
     */
    public function testReindexAll()
    {
        $indexerCollectionFactory = Bootstrap::getObjectManager()->create(IndexerCollectionFactory::class);
        $indexers = $indexerCollectionFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            $indexer->invalidate();
        }

        $this->processor->reindexAll();

        $notValidIndexers = [];
        $indexers = $indexerCollectionFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            if ($indexer->isValid()) {
                continue;
            }

            $notValidIndexers[] = $indexer->getId();
        }
        $this->assertEmpty(
            $notValidIndexers,
            'Following indexers are not valid: ' . implode(', ', $notValidIndexers)
        );
    }
}
