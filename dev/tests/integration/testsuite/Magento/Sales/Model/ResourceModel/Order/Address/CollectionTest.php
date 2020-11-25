<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Address;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddress;
use Magento\Backend\Model\Locale\Resolver;
use Magento\Framework\Locale\ResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for address collection
 *
 * @magentoAppArea adminhtml
 */
class CollectionTest extends TestCase
{
    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(Resolver::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, ResolverInterface::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, Resolver::class);

        $addressData = [
            OrderAddress::REGION => 'Alabama',
            OrderAddress::REGION_ID => '1',
            OrderAddress::POSTCODE => '11111',
            OrderAddress::LASTNAME => 'lastname',
            OrderAddress::FIRSTNAME => 'firstname',
            OrderAddress::STREET => 'street',
            OrderAddress::CITY => 'Montgomery',
            OrderAddress::EMAIL => 'admin@example.com',
            OrderAddress::TELEPHONE => '11111111',
            OrderAddress::COUNTRY_ID => 'US'
        ];
        $billingAddress = Bootstrap::getObjectManager()->create(OrderAddress::class, ['data' => $addressData]);
        $billingAddress->setAddressType('billing');
        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');
        $payment = Bootstrap::getObjectManager()->create(Payment::class);
        $payment->setMethod('payflowpro')
            ->setCcExpMonth('5')
            ->setCcLast4('0005')
            ->setCcType('AE')
            ->setCcExpYear('2022');
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->setIncrementId('100000001')
            ->setSubtotal(100)
            ->setBaseSubtotal(100)
            ->setCustomerEmail('admin@example.com')
            ->setCustomerIsGuest(true)
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setStoreId(Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->getStore()->getId())
            ->setPayment($payment);
        $order->save();
    }

    /**
     * @magentoDataFixture Magento/Directory/_files/region_name_jp.php
     */
    public function testCollectionWithJpLocale(): void
    {
        $locale = 'JA_jp';
        $this->localeResolverMock->method('getLocale')->willReturn($locale);

        $order = Bootstrap::getObjectManager()->create(Order::class)
            ->loadByIncrementId('100000001');

        $collection = $order->getAddressesCollection();
        foreach ($collection as $address) {
            $this->assertEquals('アラバマ', $address->getData(OrderAddress::REGION));
        }
    }
}
