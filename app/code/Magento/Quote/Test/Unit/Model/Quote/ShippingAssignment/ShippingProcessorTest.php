<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\ShippingAssignment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ShippingProcessorTest
 */
class ShippingProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingAddressManagement|MockObject
     */
    private $shippingAddressManagement;

    /**
     * @var ShippingMethodManagement|MockObject
     */
    private $shippingMethodManagement;

    /**
     * @var ShippingProcessor
     */
    private $shippingProcessor;

    protected function setUp()
    {
        $this->shippingAddressManagement = $this->getMockBuilder(ShippingAddressManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['assign'])
            ->getMock();
        
        $this->shippingMethodManagement = $this->getMockBuilder(ShippingMethodManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
            ->getMock();
        
        $objectManager = new ObjectManager($this);
        
        $this->shippingProcessor = $objectManager->getObject(ShippingProcessor::class, [
            'shippingAddressManagement' => $this->shippingAddressManagement,
            'shippingMethodManagement' => $this->shippingMethodManagement
        ]);
    }

    /**
     * @param string $method
     * @param string $carrierCode
     * @param string $methodCode
     * @dataProvider saveDataProvider
     */
    public function testSave($method, $carrierCode, $methodCode)
    {
        $shipping = $this->getMockForAbstractClass(ShippingInterface::class);
        $quote = $this->getMockForAbstractClass(CartInterface::class);
        $quoteId = 1;

        $address = $this->getMockForAbstractClass(AddressInterface::class);
        
        $quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);
        
        $shipping->expects($this->once())
            ->method('getAddress')
            ->willReturn($address);

        $this->shippingAddressManagement->expects($this->once())
            ->method('assign')
            ->with($quoteId, $address);

        $shipping->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn($method);

        $quote->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->shippingMethodManagement->expects($this->once())
            ->method('apply')
            ->with($quoteId, $carrierCode, $methodCode);

        $this->shippingProcessor->save($shipping, $quote);
    }

    /**
     * Get variations for save method testing
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            ['carrier_Global_World_Economy', 'carrier', 'Global_World_Economy'],
            ['carrier_International_Economy', 'carrier', 'International_Economy'],
            ['carrier_Express', 'carrier', 'Express'],
            ['flat_rate', 'flat', 'rate'],
        ];
    }
}
