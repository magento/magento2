<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Indexer\StateInterfaceFactory;
use Magento\Indexer\Model\ResourceModel\Indexer\State as StateResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;
use Magento\TestFramework\ObjectManager;

/**
 * Integration tests for \Magento\Catalog\Model\Indexer\Product\Flat\Processor.
 */
class ProcessorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StateResource
     */
    private $stateResource;

    /**
     * @var StateInterfaceFactory;
     */
    private $stateFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->state = $this->objectManager->get(State::class);
        $this->processor = $this->objectManager->get(Processor::class);
        $this->stateResource = $this->objectManager->get(StateResource::class);
        $this->stateFactory = $this->objectManager->get(StateInterfaceFactory::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testEnableProductFlat(): void
    {
        $this->assertTrue($this->state->isFlatEnabled());
        $this->assertTrue($this->processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testSaveAttribute(): void
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $productResource = $product->getResource();
        $productResource->getAttribute('sku')->setData('used_for_sort_by', 1)->save();
        $this->assertTrue($this->processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testDeleteAttribute(): void
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        /** @var Repository $productAttributeRepository */
        $productAttributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(Repository::class);
        $productAttrubute = $productAttributeRepository->get('flat_attribute');
        $productAttributeId = $productAttrubute->getAttributeId();
        $model->load($productAttributeId)->delete();
        $this->assertTrue($this->processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testAddNewStore(): void
    {
        $this->assertTrue($this->processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     *
     * @return void
     */
    public function testAddNewStoreGroup(): void
    {
        /** @var \Magento\Store\Model\Group $storeGroup */
        $storeGroup = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\Group::class
        );
        $storeGroup->setData(
            ['website_id' => 1, 'name' => 'New Store Group', 'root_category_id' => 2, 'group_id' => null]
        );
        $storeGroup->save();
        $this->assertTrue($this->processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 0
     *
     * @return void
     */
    public function testReindexAllWithProductFlatDisabled(): void
    {
        $this->updateIndexerStatus();
        $this->processor->reindexAll();
        $state = $this->getIndexerState();
        $this->assertEquals(StateInterface::STATUS_INVALID, $state->getStatus());
    }

    /**
     * Update status for indexer
     *
     * @param string $status
     * @return void
     */
    private function updateIndexerStatus(string $status = StateInterface::STATUS_INVALID): void
    {
        $state = $this->getIndexerState();
        $state->setStatus($status);
        $this->stateResource->save($state);
    }

    /**
     * Get Indexer state
     *
     * @return StateInterface
     */
    private function getIndexerState(): StateInterface
    {
        $state = $this->stateFactory->create();

        return $state->loadByIndexer(State::INDEXER_ID);
    }
}
