<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Address;

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
     * @var CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(Resolver::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, ResolverInterface::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, Resolver::class);

        $this->addressCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Directory/_files/region_name_jp.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCollectionWithJpLocale()
    {
        $locale = 'JA_jp';
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $order = Bootstrap::getObjectManager()->create(Order::class)
            ->loadByIncrementId('100000001');
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
        $order->setBillingAddress($billingAddress)->setShippingAddress($shippingAddress)->save();

        $collection = $this->addressCollectionFactory->create()->setOrderFilter($order);
        foreach ($collection as $address) {
            $this->assertEquals('アラバマ', $address->getData(OrderAddress::REGION));
        }
    }
}
