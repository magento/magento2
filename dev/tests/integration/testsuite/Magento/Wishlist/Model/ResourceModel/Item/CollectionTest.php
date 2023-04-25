<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\ResourceModel\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Wishlist\Model\Wishlist;
use Magento\Catalog\Model\Attribute\Config;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Collection
     */
    private $itemCollection;

    /**
     * @var Wishlist
     */
    private $wishlist;

    /**
     * @var Config\Data
     */
    private $attributeConfig;

    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->wishlist = $this->objectManager->create(Wishlist::class);
        $this->itemCollection = $this->objectManager->get(Collection::class);
        $this->attributeConfig = $this->objectManager->get(Config\Data::class);
    }

    /**
     * Verify that Wishlist Item Collection uses Catalog Attributes defined in the configuration.
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_shared.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testLoadedProductAttributes()
    {
        $this->addAttributesToWishlistConfig([
            'short_description',
        ]);
        $this->wishlist->loadByCode('fixture_unique_code');
        $this->itemCollection->addWishlistFilter($this->wishlist);

        /** @var Product $productOnWishlist */
        $productOnWishlist = $this->itemCollection->getFirstItem()->getProduct();
        $this->assertEquals('Simple Product', $productOnWishlist->getName());
        $this->assertEquals('Short description', $productOnWishlist->getData('short_description'));
    }

    /**
     * Tests collection load.
     * Tests collection load method when product salable filter flag is setted to true
     * and few products are present.
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     * @magentoDbIsolation disabled
     */
    public function testLoadWhenFewProductsPresent()
    {
        $this->itemCollection->setSalableFilter(true);
        $this->itemCollection->addCustomerIdFilter(1);
        $this->itemCollection->load();
        $this->assertCount(1, $this->itemCollection->getItems());
    }

    /**
     * @param array $attributes
     */
    private function addAttributesToWishlistConfig($attributes)
    {
        $this->attributeConfig->merge([
            'wishlist_item' => $attributes
        ]);
    }
}
