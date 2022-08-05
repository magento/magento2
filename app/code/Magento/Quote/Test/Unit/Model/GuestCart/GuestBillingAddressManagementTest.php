<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Model\GuestCart\GuestBillingAddressManagement;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestBillingAddressManagementTest extends TestCase
{
    /**
     * @var GuestBillingAddressManagement
     */
    protected $model;

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
    protected $billingAddressManagementMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->addressMock = $this->createMock(Address::class);
        $this->billingAddressManagementMock = $this->createMock(
            BillingAddressManagementInterface::class
        );

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            GuestBillingAddressManagement::class,
            [
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'billingAddressManagement' => $this->billingAddressManagementMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $this->billingAddressManagementMock->expects($this->once())->method('get')->willReturn($this->addressMock);
        $this->assertEquals($this->addressMock, $this->model->get($this->maskedCartId));
    }

    /**
     * @return void
     */
    public function testAssign()
    {
        $addressId = 1;
        $this->billingAddressManagementMock->expects($this->once())->method('assign')->willReturn($addressId);
        $actualAddressId = $this->model->assign($this->maskedCartId, $this->addressMock);
        $this->assertIsInt($actualAddressId);
        $this->assertEquals($addressId, $actualAddressId);
    }
}
