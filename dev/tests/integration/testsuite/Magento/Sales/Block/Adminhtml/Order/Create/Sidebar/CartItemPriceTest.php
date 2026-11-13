<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('adminhtml')]
class CartItemPriceTest extends TestCase
{
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(Quote::class)->clearStorage();
        parent::tearDown();
    }

    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 10,
                'options' => [
                    [
                        'type' => 'drop_down',
                        'title' => 'Option',
                        'is_require' => true,
                        'values' => [
                            ['title' => 'Fixed', 'price' => 5, 'price_type' => 'fixed', 'sku' => 'opt-fixed'],
                        ],
                    ],
                ],
            ],
            as: 'product'
        ),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
    ]
    public function testGetItemPriceIncludesCustomOptionsPrice(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $storage = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $product = $storage->get('product');
        $quote = $storage->get('cart');
        $option = $product->getOptions()[0];
        $values = $option->getValues();
        $quote->addProduct(
            $product,
            new DataObject(['qty' => 1, 'options' => [$option->getOptionId() => reset($values)->getOptionTypeId()]])
        );
        $quote->collectTotals();
        $objectManager->get(CartRepositoryInterface::class)->save($quote);

        $objectManager->get(Quote::class)->setCustomerId((int)$storage->get('customer')->getId());
        $block = $objectManager->get(LayoutInterface::class)->createBlock(Cart::class);
        $items = $block->getItemCollection();

        $this->assertCount(1, $items);
        $this->assertStringContainsString('15.00', $block->getItemPrice($block->getProduct(reset($items))));
    }
}
