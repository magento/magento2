<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\Plugin;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $cartMock;

    /**
     * @var Plugin
     */
    protected $model;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getCheckoutState', 'setCheckoutState'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock = $this->createMock(Cart::class);
        $this->model = new Plugin($this->checkoutSessionMock);
    }

    public function testBeforeInitCaseTrue()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn(State::STEP_SELECT_ADDRESSES);
        $this->checkoutSessionMock->expects($this->once())->method('setCheckoutState')
            ->with(Session::CHECKOUT_STATE_BEGIN);
        $this->model->beforeSave($this->cartMock);
    }

    public function testBeforeInitCaseFalse()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn('');
        $this->checkoutSessionMock->expects($this->never())->method('setCheckoutState');
        $this->model->beforeSave($this->cartMock);
    }
}
