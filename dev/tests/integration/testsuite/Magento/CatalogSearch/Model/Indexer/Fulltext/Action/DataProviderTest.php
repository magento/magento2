<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testRebuildStoreIndexConfigurable()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $repository = $objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->create(
            \Magento\Store\Model\Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $repository->get('simple');
        $productId = $product->getId();
        $productResource = $product->getResource();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setShortDescription('short description 2 store');
        $productResource->save($product);

        $this->assertEquals(
            'Short description',
            $productResource->getAttributeRawValue($productId, 'short_description', $globalStoreId)
        );
        $this->assertEquals(
            'short description 2 store',
            $productResource->getAttributeRawValue($productId, 'short_description', $secondStoreId)
        );
        $this->assertEquals(
            'Short description',
            $productResource->getAttributeRawValue($productId, 'short_description', $thirdStoreId)
        );
    }
}
