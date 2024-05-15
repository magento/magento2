<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\AdminOrder;

use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Reorder with Bundle product integration tests.
 *
 * @see Create
 * @magentoAppArea adminhtml
 */
class ReorderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Create
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model =$this->objectManager->get(Create::class);
    }

    /**
     * Check Custom Price after reordering with Bundle product.
     *
     * @return void
     * @magentoDataFixture Magento/Bundle/_files/order_item_with_bundle_and_options.php
     */
    public function testReorderBundleProductWithCustomPrice(): void
    {
        $customPrice = 300;
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        $this->model->initFromOrder($order);

        /** @var QuoteItem[] $quoteItems */
        $quoteItems = $this->model->getQuote()->getAllItems();
        $firstQuoteItem = array_shift($quoteItems);
        self::assertNull($firstQuoteItem->getParentItemId());
        self::assertEquals($customPrice, (int)$firstQuoteItem->getCustomPrice());
        foreach ($quoteItems as $quoteItem) {
            self::assertEquals($firstQuoteItem->getId(), $quoteItem->getParentItemId());
            self::assertEquals(0, (int)$quoteItem->getCustomPrice());
        }

        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getGroupId',
                    'getEmail',
                    '_getExtensionAttributes'
                ]
            )->getMock();
        $customerMock->method('getGroupId')
            ->willReturn(1);
        $customerMock->method('getEmail')
            ->willReturn('customer@example.com');
        $customerMock->method('_getExtensionAttributes')
            ->willReturn(null);
        $this->model->getQuote()->setCustomer($customerMock);

        $shippingMethod = 'freeshipping_freeshipping';
        /** @var Rate $rate */
        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode($shippingMethod);
        $this->model->getQuote()->getShippingAddress()->addShippingRate($rate);
        $this->model->setPaymentData(['method' => 'checkmo']);
        $this->model->setIsValidate(true)->importPostData(['shipping_method' => $shippingMethod]);
        $newOrder = $this->model->createOrder();

        /** @var OrderItem[] $orderItems */
        $orderItems = $newOrder->getAllItems();
        $firstOrderItem = array_shift($orderItems);
        self::assertNull($firstOrderItem->getParentItemId());
        self::assertEquals($customPrice, (int)$firstOrderItem->getPrice());
        foreach ($orderItems as $orderItem) {
            self::assertEquals($firstOrderItem->getId(), $orderItem->getParentItemId());
            self::assertEquals(0, (int)$orderItem->getPrice());
        }
    }
}
