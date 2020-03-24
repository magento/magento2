<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Account;

use Magento\Checkout\Controller\Account\DelegateCreate;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Api\OrderCustomerDelegateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DelegateCreateTest extends TestCase
{
    private const STUB_ORDER_ID = 5;

    /** @var MockObject|Session */
    private $sessionMock;

    /** @var MockObject|RedirectFactory */
    private $redirectFactoryMock;

    /** @var MockObject|Redirect */
    private $redirectMock;

    /** @var MockObject|OrderCustomerDelegateInterface */
    private $delegateServiceMock;

    /** @var DelegateCreate */
    private $delegateCreate;

    protected function setUp()
    {
        $this->sessionMock = $this->createPartialMock(Session::class, ['getLastOrderId']);
        $this->redirectFactoryMock = $this->createPartialMock(RedirectFactory::class, ['create']);
        $this->redirectMock = $this->createPartialMock(Redirect::class, ['setPath']);
        $this->redirectFactoryMock->method('create')->willReturn($this->redirectMock);
        $this->delegateServiceMock = $this->getMockBuilder(OrderCustomerDelegateInterface::class)
            ->setMethods(['delegateNew'])
            ->getMockForAbstractClass();

        $this->delegateCreate = new DelegateCreate(
            $this->delegateServiceMock,
            $this->sessionMock,
            $this->redirectFactoryMock
        );
    }

    public function testWhenOrderPlacedDelegateRequest()
    {
        // Given
        $this->sessionMock->method('getLastOrderId')
            ->willReturn(self::STUB_ORDER_ID);

        // Expects
        $this->delegateServiceMock->expects($this->once())
            ->method('delegateNew')
            ->with(self::STUB_ORDER_ID);

        // When
        $this->delegateCreate->execute();
    }

    public function testWhenOrderMissingRedirectHome()
    {
        // Given
        $this->sessionMock->method('getLastOrderId')
            ->willReturn(null);

        // Expects
        $this->delegateServiceMock->expects($this->never())
            ->method('delegateNew');
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('/');

        // When
        $this->delegateCreate->execute();
    }
}
