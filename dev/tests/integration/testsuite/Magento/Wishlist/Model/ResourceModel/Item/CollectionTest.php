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

class CollectionTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
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
     * @magentoDbIsolation enabled
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
     * @param array $attributes
     */
    private function addAttributesToWishlistConfig($attributes)
    {
        $this->attributeConfig->merge([
            'wishlist_item' => $attributes
        ]);
    }
}
