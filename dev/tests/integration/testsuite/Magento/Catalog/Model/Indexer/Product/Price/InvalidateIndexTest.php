<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the invalidate price index model is working correctly
 *
 * @see \Magento\Catalog\Model\Indexer\Product\Price\InvalidateIndex
 */
class InvalidateIndexTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var UpdateIndexInterface */
    private $invalidatePriceIndex;

    /** @var Processor */
    private $priceIndexerProcessor;

    /** @var GroupInterfaceFactory */
    private $customerGroupDataFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->invalidatePriceIndex = $this->objectManager->get(InvalidateIndex::class);
        $this->priceIndexerProcessor = $this->objectManager->get(Processor::class);
        $this->customerGroupDataFactory = $this->objectManager->get(GroupInterfaceFactory::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testUpdate(): void
    {
        $this->priceIndexerProcessor->reindexAll();
        $this->assertTrue($this->priceIndexerProcessor->getIndexer()->isValid());
        $customerGroupData = $this->customerGroupDataFactory->create();
        $this->invalidatePriceIndex->update($customerGroupData, true);
        $this->assertTrue($this->priceIndexerProcessor->getIndexer()->isInvalid());
    }
}
