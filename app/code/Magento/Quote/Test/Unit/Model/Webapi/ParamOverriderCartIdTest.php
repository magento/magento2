<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Webapi\ParamOverriderCartId;

/**
 * Test for \Magento\Quote\Model\Webapi\ParamOverriderCartId
 */
class ParamOverriderCartIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ParamOverriderCartId
     */
    private $model;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->cartManagement = $this->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Quote\Model\Webapi\ParamOverriderCartId::class,
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
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($customerId));

        $cart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->getMockForAbstractClass();
        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->will($this->returnValue($cart));
        $cart->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($retValue));

        $this->assertSame($retValue, $this->model->getOverriddenValue());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetOverriddenValueIsCustomerAndCartDoesNotExist()
    {
        $customerId = 1;

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($customerId));

        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->will($this->throwException(new NoSuchEntityException()));

        $this->model->getOverriddenValue();
    }

    public function testGetOverriddenValueIsCustomerAndCartIsNull()
    {
        $customerId = 1;

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($customerId));

        $this->cartManagement->expects($this->once())
            ->method('getCartForCustomer')
            ->with($customerId)
            ->will($this->returnValue(null));

        $this->assertNull($this->model->getOverriddenValue());
    }

    public function testGetOverriddenValueIsNotCustomer()
    {
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_ADMIN));

        $this->assertNull($this->model->getOverriddenValue());
    }
}
