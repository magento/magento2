<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Block\Order\Email\Items\Order;

use Magento\GroupedProduct\ViewModel\Order\Email\Items\Creditmemo\ItemPriceRender;
use Magento\Sales\Block\Order\Email\Items\DefaultItems;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify grouped product block will output correct data.
 *
 * @magentoAppArea frontend
 */
class GroupedTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var Grouped
     */
    private $block;

    /**
     * @var CreditMemo
     */
    private $creditMemo;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(Grouped::class);
        $this->creditMemo = $this->objectManager->get(CreditMemo::class);
    }

    /**
     * Verify, grouped block will output correct product sku and name.
     *
     * @magentoDataFixture Magento/Sales/_files/creditmemo_with_grouped_product.php
     */
    public function testToHtml()
    {
        $creditMemo = $this->creditMemo->load('100000002', 'increment_id');
        $creditMemoItem = $creditMemo->getItemsCollection()->getFirstItem();
        $priceBlock = $this->objectManager->create(DefaultItems::class);
        $itemPriceRender = $this->objectManager->create(ItemPriceRender::class);
        $this->block->setTemplate('Magento_Sales::email/items/creditmemo/default.phtml');
        $this->block->setItemPriceRender($itemPriceRender);
        $this->block->setItem($creditMemoItem);
        $this->block->getLayout()->setBlock('item_price', $priceBlock);
        $output = $this->block->toHtml();
        self::assertStringContainsString('SKU: simple_11', $output);
        self::assertStringContainsString('"product-name">Simple 11', $output);
    }
}
