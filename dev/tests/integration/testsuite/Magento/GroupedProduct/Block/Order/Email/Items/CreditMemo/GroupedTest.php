<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Block\Order\Email\Items\CreditMemo;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->block = Bootstrap::getObjectManager()->get(Grouped::class);
        $this->creditMemo = Bootstrap::getObjectManager()->get(CreditMemo::class);
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
        $priceBlock = Bootstrap::getObjectManager()->create(DefaultItems::class);
        $this->block->setTemplate('Magento_Sales::email/items/creditmemo/default.phtml');
        $this->block->setItem($creditMemoItem);
        $this->block->getLayout()->setBlock('item_price', $priceBlock);
        $output = $this->block->toHtml();
        self::assertStringContainsString('SKU: simple_11', $output);
        self::assertStringContainsString('"product-name">Simple 11', $output);
    }
}
