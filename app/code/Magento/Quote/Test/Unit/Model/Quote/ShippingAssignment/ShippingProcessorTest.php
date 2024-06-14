<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\ShippingAssignment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingProcessorTest extends TestCase
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

    protected function setUp(): void
    {
        $this->shippingAddressManagement = $this->getMockBuilder(ShippingAddressManagement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['assign'])
            ->getMock();

        $this->shippingMethodManagement = $this->getMockBuilder(ShippingMethodManagement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['apply'])
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

        $quote->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $shipping->expects(static::once())
            ->method('getAddress')
            ->willReturn($address);

        $this->shippingAddressManagement->expects(static::once())
            ->method('assign')
            ->with($quoteId, $address);

        $shipping->expects(static::exactly(2))
            ->method('getMethod')
            ->willReturn($method);

        $quote->expects(static::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->shippingMethodManagement->expects(static::once())
            ->method('apply')
            ->with($quoteId, $carrierCode, $methodCode);

        $this->shippingProcessor->save($shipping, $quote);
    }

    /**
     * Get variations for save method testing
     * @return array
     */
    public static function saveDataProvider()
    {
        return [
            ['carrier_Global_World_Economy', 'carrier', 'Global_World_Economy'],
            ['carrier_International_Economy', 'carrier', 'International_Economy'],
            ['carrier_Express', 'carrier', 'Express'],
            ['flat_rate', 'flat', 'rate'],
        ];
    }
}
