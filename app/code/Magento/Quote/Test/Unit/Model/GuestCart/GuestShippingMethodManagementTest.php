<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\GuestCart\GuestShippingMethodManagement;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\Quote\Model\GuestCart\GuestShippingMethodManagement::class)]
class GuestShippingMethodManagementTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GuestShippingMethodManagement
     */
    private $model;

    /**
     * @var MockObject
     */
    private $shippingMethodManagementMock;

    /**
     * @var MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @var ShipmentEstimationInterface|MockObject
     */
    private $shipmentEstimationManagement;

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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->shippingMethodManagementMock =
            $this->createMock(ShippingMethodManagement::class);

        // Create QuoteIdMask mock
        $this->quoteIdMask = $this->createPartialMockWithReflection(QuoteIdMask::class, ["load", "getQuoteId"]);
        $this->quoteIdMask->method('load')->with($this->maskedCartId)->willReturnSelf();
        $this->quoteIdMask->method('getQuoteId')->willReturn($this->cartId);
        
        // Create QuoteIdMaskFactory mock
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskFactoryMock->method('create')->willReturn($this->quoteIdMask);

        $this->shipmentEstimationManagement = $this->createMock(ShipmentEstimationInterface::class);

        $this->model = $objectManager->getObject(
            GuestShippingMethodManagement::class,
            [
                'shippingMethodManagement' => $this->shippingMethodManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
            ]
        );

        $refObject = new \ReflectionClass(GuestShippingMethodManagement::class);
        $refProperty = $refObject->getProperty('shipmentEstimationManagement');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->shipmentEstimationManagement);
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

    public function testEstimateByExtendedAddress()
    {
        $address = $this->createMock(AddressInterface::class);

        $methodObject = $this->createMock(ShippingMethodInterface::class);
        $expectedRates = [$methodObject];

        $this->shipmentEstimationManagement->expects(static::once())
            ->method('estimateByExtendedAddress')
            ->with($this->cartId, $address)
            ->willReturn($expectedRates);

        $carriersRates = $this->model->estimateByExtendedAddress($this->maskedCartId, $address);
        static::assertEquals($expectedRates, $carriersRates);
    }
}
