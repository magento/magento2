<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for remove quote items plugin.
 *
 * @see \Magento\Wishlist\Plugin\Model\ResourceModel\Product
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class ProductTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductResourceModel */
    private $productResoure;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResoure = $this->objectManager->get(ProductResourceModel::class);
        $this->getWishlistByCustomerId = $this->objectManager->get(GetWishlistByCustomerId::class);
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void
    {
        $pluginInfo = $this->objectManager->get(PluginList::class)->get(ProductResourceModel::class);
        $this->assertSame(
            Product::class,
            $pluginInfo['cleanups_wishlist_item_after_product_delete']['instance']
        );
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testDeleteProduct(): void
    {
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $this->productResoure->delete($item->getProduct());
        $this->assertNull($this->getWishlistByCustomerId->getItemBySku(1, 'simple'));
    }
}
