<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class ProductAttributesCleanUpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Catalog\Console\Command\ProductAttributesCleanUp
     */
    private $command;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     */
    private $attributeResource;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->command = $this->objectManager->create('Magento\Catalog\Console\Command\ProductAttributesCleanUp');
        $this->attributeResource = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Attribute');
        $this->tester = new CommandTester($this->command);

        // Prepare data fixtures for test
        $store = $this->prepareAdditionalStore();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $product = $productRepository->get('simple');
        $product->setName('Simple fixture store');
        $product->setStoreId($store->getId());
        $product->save();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/fixture_store_with_catalogsearch_index.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExecute()
    {
        // Verify that unused attribute was created
        $attribute = $this->getUnusedProductAttribute();

        $this->assertArrayHasKey('value', $attribute);
        $this->assertArrayHasKey('value_id', $attribute);
        $this->assertEquals($attribute['value'], 'Simple fixture store');

        // Execute command
        $this->tester->execute([]);

        // Verify that unused attribute was removed
        $this->assertContains('Unused product attributes successfully cleaned up', $this->tester->getDisplay());
        $attribute = $this->getUnusedProductAttribute();
        $this->assertFalse($attribute);
    }

    /**
     * @return array|false
     */
    private function getUnusedProductAttribute()
    {
        $connection = $this->attributeResource->getConnection();
        $select = $connection->select();
        $select->from($this->attributeResource->getTable('catalog_product_entity_varchar'));
        $select->where('value = ?', 'Simple fixture store');
        return $connection->fetchRow($select);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    private function prepareAdditionalStore()
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->objectManager->create('Magento\Store\Model\Website');
        $website->load('test');

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create('Magento\Store\Model\Store');
        $store->load('fixturestore');

        /** @var \Magento\Store\Model\Group $storeGroup */
        $storeGroup = $this->objectManager->create('Magento\Store\Model\Group');
        $storeGroup->setWebsiteId($website->getId());
        $storeGroup->setName('Fixture Store Group');
        $storeGroup->setRootCategoryId(2);
        $storeGroup->setDefaultStoreId($store->getId());
        $storeGroup->save();

        $store->setWebsiteId($website->getId())
            ->setGroupId($storeGroup->getId())
            ->save();

        /* Refresh stores memory cache */
        $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
        return $store;
    }
}
