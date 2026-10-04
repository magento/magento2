<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Webapi\ParamOverriderCartId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Quote\Model\Webapi\ParamOverriderCartId
 */
class ParamOverriderCartIdTest extends TestCase
{
    /**
     * @var ParamOverriderCartId
     */
    private $model;

    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var MockObject
     */
    private $cartManagement;

    protected function setUp(): void
    {
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->cartManagement = $this->createMock(CartManagementInterface::class);
        $this->model = (new ObjectManager($this))->getObject(
            ParamOverriderCartId::class,
            [
                'userContext' => $this->userContext,
                'cartManagement' => $this->cartManagement,
            ]
        );
    }

    public function testGetOverriddenValueIsCustomerAndCartExists()
    {
        $retValue = 'retValue';
        $customerId = 1;

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $cart = $this->createMock(CartInterface::class);
        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->willReturn($cart);
        $cart->expects($this->once())
            ->method('getId')
            ->willReturn($retValue);

        $this->assertSame($retValue, $this->model->getOverriddenValue());
    }

    public function testGetOverriddenValueIsCustomerAndCartDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $customerId = 1;

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $this->model->getOverriddenValue();
    }

    public function testGetOverriddenValueIsCustomerAndCartIsNull()
    {
        $customerId = 1;

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->willReturn(null);

        $this->assertNull($this->model->getOverriddenValue());
    }

    public function testGetOverriddenValueIsNotCustomer()
    {
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->assertNull($this->model->getOverriddenValue());
    }
}
