<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\OneTouchOrdering\Model\RateCheck;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\Address;

class RateCheckTest extends TestCase
{
    /**
     * @var  RateRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $website;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateCollector;
    /**
     * @var RateCheck
     */
    private $rateCheck;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $rateRequestFactory = $this->createMock(RateRequestFactory::class);
        $rateRequestFactory->method('create')->willReturn($this->request);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $this->store = $this->createMock(Store::class);
        $this->website = $this->createMock(\Magento\Store\Api\Data\WebsiteInterface::class);

        $storeManager->method('getStore')->willReturn($this->store);
        $storeManager->method('getWebsite')->willReturn($this->website);

        $rateCollectorFactory = $this->createMock(RateCollectorInterfaceFactory::class);
        $this->rateCollector = $this->getMockBuilder(RateCollectorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['collectRates', 'getResult'])
            ->getMock();
        $this->rateCollector->method('collectRates')->willReturnSelf();

        $rateCollectorFactory->method('create')->willReturn($this->rateCollector);
        $this->rateCheck = $objectManager->getObject(
            RateCheck::class,
            [
                'rateRequestFactory' => $rateRequestFactory,
                'storeManager' => $storeManager,
                'rateCollector' => $rateCollectorFactory
            ]
        );
    }

    public function testGetRatesRequest()
    {
        $storeId = 123;
        $websiteId = 234;

        $addressData = [
            'country_id' => 123,
            'region_id' => 234,
            'region_code' => 'test region code',
            'street' => 'test street',
            'city' => 'test city',
            'postcode' => '12345',
        ];

        $destData = [
            'dest_country_id' => 123,
            'dest_region_id' => 234,
            'dest_region_code' => 'test region code',
            'dest_street' => 'test street',
            'dest_city' => 'test city',
            'dest_postcode' => '12345',
            'store_id' => $storeId,
            'website_id' => $websiteId,
            'base_currency' => 'base_currency',
            'package_currency' => 'current_currency'
        ];
        /** @var Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionCode'])
            ->getMock();

        $address->setData($addressData);
        $address->method('getRegionCode')->willReturn($addressData['region_code']);
        $this->store->expects($this->once())->method('getBaseCurrency')->willReturn('base_currency');
        $this->store
            ->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn('current_currency');
        $this->store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->website->expects($this->once())->method('getId')->willReturn($websiteId);

        $this->rateCollector->expects($this->once())->method('getResult')->willReturn(false);
        
        $this->rateCheck->getRatesForCustomerAddress($address);
        $this->assertArraySubset($destData, $this->request->getData());
    }

    public function testGetRatesResultFalse()
    {
        /** @var Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionCode'])
            ->getMock();

        $this->rateCollector->expects($this->once())->method('getResult')->willReturn(false);
        $result = $this->rateCheck->getRatesForCustomerAddress($address);
        $this->assertTrue(empty($result));
    }

    public function testGetRatesResultTrue()
    {
        /** @var Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionCode'])
            ->getMock();

        $resultMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllRates'])
            ->getMock();

        $resultMock->expects($this->once())->method('getAllRates')->willReturn([true]);
        $this->rateCollector->expects($this->once())->method('getResult')->willReturn($resultMock);
        $result = $this->rateCheck->getRatesForCustomerAddress($address);
        $this->assertEquals($result, [true]);
    }
}
