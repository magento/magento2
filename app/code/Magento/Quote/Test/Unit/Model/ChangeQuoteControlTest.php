<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\ChangeQuoteControl;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Unit test for \Magento\Quote\Model\ChangeQuoteControl
 *
 * Class \Magento\Quote\Test\Unit\Model\ChangeQuoteControlTest
 */
class ChangeQuoteControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Quote\Model\ChangeQuoteControl
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->userContextMock = $this->createMock(UserContextInterface::class);

        $this->model = $this->objectManager->getObject(
            ChangeQuoteControl::class,
            [
                'userContext' => $this->userContextMock
            ]
        );

        $this->quoteMock = $this->getMockForAbstractClass(
            CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomerId']
        );
    }

    /**
     * Test if the quote is belonged to customer
     */
    public function testIsAllowedIfTheQuoteIsBelongedToCustomer()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($quoteCustomerId));
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContextMock->expects($this->any())->method('getUserId')
            ->will($this->returnValue($quoteCustomerId));

        $this->assertEquals(true, $this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is not belonged to customer
     */
    public function testIsAllowedIfTheQuoteIsNotBelongedToCustomer()
    {
        $currentCustomerId = 1;
        $quoteCustomerId = 2;

        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($quoteCustomerId));
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContextMock->expects($this->any())->method('getUserId')
            ->will($this->returnValue($currentCustomerId));

        $this->assertEquals(false, $this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is belonged to guest and the context is guest
     */
    public function testIsAllowedIfQuoteIsBelongedToGuestAndContextIsGuest()
    {
        $quoteCustomerId = null;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($quoteCustomerId));
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_GUEST));
        $this->assertEquals(true, $this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the quote is belonged to customer and the context is guest
     */
    public function testIsAllowedIfQuoteIsBelongedToCustomerAndContextIsGuest()
    {
        $quoteCustomerId = 1;
        $this->quoteMock->expects($this->any())->method('getCustomerId')
            ->will($this->returnValue($quoteCustomerId));
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_GUEST));
        $this->assertEquals(false, $this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the context is admin
     */
    public function testIsAllowedIfContextIsAdmin()
    {
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_ADMIN));
        $this->assertEquals(true, $this->model->isAllowed($this->quoteMock));
    }

    /**
     * Test if the context is integration
     */
    public function testIsAllowedIfContextIsIntegration()
    {
        $this->userContextMock->expects($this->any())->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_INTEGRATION));
        $this->assertEquals(true, $this->model->isAllowed($this->quoteMock));
    }
}
