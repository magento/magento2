<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PdfDocumentsMassActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\PdfDocumentsMassAction
     */
    private $controller;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->messageManager = $this->getMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );

        $this->orderCollectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->filterMock = $this->getMock(\Magento\Ui\Component\MassAction\Filter::class, [], [], '', false);

        $this->orderCollectionFactoryMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->orderCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->resultRedirect = $this->getMock(\Magento\Backend\Model\View\Result\Redirect::class, [], [], '', false);
        $resultRedirectFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            [],
            [],
            '',
            false
        );
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
        $this->controller = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Pdfinvoices::class,
            [
                'filter' => $this->filterMock,
                'resultFactory' => $resultRedirectFactory,
                'messageManager' => $this->messageManager
            ]
        );
        $objectManagerHelper
            ->setBackwardCompatibleProperty(
                $this->controller,
                'orderCollectionFactory',
                $this->orderCollectionFactoryMock
            );
    }

    public function testExecute()
    {
        $exception = new \Exception();
        $this->filterMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($this->orderCollectionMock)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addError');

        $this->resultRedirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->controller->execute($exception);
    }
}
