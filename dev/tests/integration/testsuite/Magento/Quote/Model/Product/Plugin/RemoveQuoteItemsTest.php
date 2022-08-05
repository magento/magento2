<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for remove quote items plugin.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class RemoveQuoteItemsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductResourceModel */
    private $productResoure;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResoure = $this->objectManager->get(ProductResourceModel::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void
    {
        $pluginInfo = $this->objectManager->get(PluginList::class)->get(ProductResourceModel::class);
        $this->assertSame(
            RemoveQuoteItems::class,
            $pluginInfo['clean_quote_items_after_product_delete']['instance']
        );
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     *
     * @return void
     */
    public function testDeleteProduct(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $this->assertNotNull($quote);
        $quoteItems = $quote->getItems();
        $quoteItem = current($quoteItems);
        $this->assertNotNull($quoteItem);
        $this->productResoure->delete($quoteItem->getProduct());
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $this->assertNotNull($quote);
        $this->assertEmpty($quote->getItems());
    }
}
