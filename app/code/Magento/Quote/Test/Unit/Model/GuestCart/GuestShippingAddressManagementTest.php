<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagement;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestShippingAddressManagementTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GuestShippingAddressManagementInterface
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var MockObject
     */
    protected $shippingAddressManagementMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->shippingAddressManagementMock = $this->createMock(
            ShippingAddressManagementInterface::class
        );
        $this->quoteAddressMock = $this->createMock(Address::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        // Create QuoteIdMask mock
        $this->quoteIdMaskMock = $this->createPartialMockWithReflection(QuoteIdMask::class, ["load", "getQuoteId"]);
        $this->quoteIdMaskMock->method("load")->willReturnSelf();
        $this->quoteIdMaskMock->method("getQuoteId")->willReturn($this->cartId);
        
        // Create QuoteIdMaskFactory mock
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskFactoryMock->method("create")->willReturn($this->quoteIdMaskMock);

        $this->model = $objectManager->getObject(
            GuestShippingAddressManagement::class,
            [
                'shippingAddressManagement' => $this->shippingAddressManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testAssign()
    {
        $addressId = 1;
        $this->shippingAddressManagementMock->expects($this->once())->method('assign')->willReturn($addressId);
        $this->assertEquals($addressId, $this->model->assign($this->maskedCartId, $this->quoteAddressMock));
    }

    public function testGet()
    {
        $this->shippingAddressManagementMock->expects($this->once())->method('get')->willReturn(
            $this->quoteAddressMock
        );
        $this->assertEquals($this->quoteAddressMock, $this->model->get($this->maskedCartId));
    }
}
