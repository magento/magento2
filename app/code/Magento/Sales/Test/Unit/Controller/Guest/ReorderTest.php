<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Controller\Guest\OrderLoader;
use Magento\Sales\Controller\Guest\Reorder;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReorderTest extends TestCase
{
    /**
     * Stub Order Id
     */
    private const STUB_ORDER_ID = 1;

    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var OrderLoader|MockObject
     */
    private $orderLoaderMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ReorderHelper|MockObject
     */
    private $reorderHelperMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * Setup environment for test
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->orderLoaderMock = $this->createMock(OrderLoader::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->reorderHelperMock = $this->createMock(ReorderHelper::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->expects($this->once())->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')
            ->with(ReorderHelper::class)
            ->willReturn($this->reorderHelperMock);

        ObjectManager::setInstance($objectManagerMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->reorder = $objectManager->getObject(
            Reorder::class,
            [
                'context' => $this->contextMock,
                'orderLoader' => $this->orderLoaderMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * Test execute() with the reorder is not allowed
     */
    public function testExecuteWithReorderIsNotAllowed()
    {
        $this->orderLoaderMock->method('load')
            ->with($this->requestMock)
            ->willReturn($this->resultRedirectFactoryMock);
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn(self::STUB_ORDER_ID);
        $this->registryMock->expects($this->once())->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);

        $resultRedirectMock = $this->createMock(Redirect::class);
        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($resultRedirectMock);
        $this->reorderHelperMock->method('canReorder')->with(self::STUB_ORDER_ID)
            ->willReturn(false);

        /** Expected Error Message */
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('Reorder is not available.')->willReturnSelf();
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')->willReturnSelf();

        /** Assert result */
        $this->assertEquals($resultRedirectMock, $this->reorder->execute());
    }
}
