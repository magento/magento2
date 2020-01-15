<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\GuestCart\GuestShippingMethodManagement;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestShippingMethodManagementTest extends TestCase
{
    /**
     * @var GuestShippingMethodManagement
     */
    private $model;

    /**
     * @var ShippingMethodManagement|MockObject
     */
    private $shippingMethodManagementMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @var ShipmentEstimationInterface|MockObject
     */
    private $shipmentEstimationManagementMock;

    /**
     * @var QuoteIdMask|MockObject
     */
    private $quoteIdMask;

    /**
     * @var string
     */
    private $maskedCartId = 'f216207248d65c789b17be8545e0aa73';

    /**
     * @var int
     */
    private $cartId = 867;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->shippingMethodManagementMock = $this->createMock(ShippingMethodManagement::class);

        $guestCartTestHelper = new GuestCartTestHelper($this);
        [$this->quoteIdMaskFactoryMock, $this->quoteIdMask] = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->shipmentEstimationManagementMock = $this->getMockForAbstractClass(ShipmentEstimationInterface::class);

        $this->model = $objectManager->getObject(
            GuestShippingMethodManagement::class,
            [
                'shippingMethodManagement' => $this->shippingMethodManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'shipmentEstimationManagement' => $this->shipmentEstimationManagementMock
            ]
        );
    }

    public function testSet()
    {
        $carrierCode = 'carrierCode';
        $methodCode = 'methodCode';

        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('set')
            ->with($this->cartId, $carrierCode, $methodCode)
            ->willReturn($retValue);

        $this->assertEquals($retValue, $this->model->set($this->maskedCartId, $carrierCode, $methodCode));
    }

    public function testGetList()
    {
        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($this->cartId)
            ->willReturn($retValue);

        $this->assertEquals($retValue, $this->model->getList($this->maskedCartId));
    }

    public function testGet()
    {
        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('get')
            ->with($this->cartId)
            ->willReturn($retValue);

        $this->assertEquals($retValue, $this->model->get($this->maskedCartId));
    }

    /**
     * @covers \Magento\Quote\Model\GuestCart\GuestShippingMethodManagement::estimateByExtendedAddress
     */
    public function testEstimateByExtendedAddress()
    {
        $address = $this->getMockForAbstractClass(AddressInterface::class);

        $methodObject = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $expectedRates = [$methodObject];

        $this->shipmentEstimationManagementMock->expects(static::once())
            ->method('estimateByExtendedAddress')
            ->with($this->cartId, $address)
            ->willReturn($expectedRates);

        $carriersRates = $this->model->estimateByExtendedAddress($this->maskedCartId, $address);
        static::assertEquals($expectedRates, $carriersRates);
    }
}
