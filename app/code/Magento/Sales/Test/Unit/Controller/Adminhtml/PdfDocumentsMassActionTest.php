<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Controller\Adminhtml\Order\PdfDocumentsMassAction;
use Magento\Sales\Controller\Adminhtml\Order\Pdfinvoices;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PdfDocumentsMassActionTest extends TestCase
{
    /**
     * @var PdfDocumentsMassAction
     */
    private $controller;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirect;

    /**
     * @var Manager|MockObject
     */
    private $messageManager;

    /**
     * @var MockObject
     */
    private $orderCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $orderCollectionMock;

    /**
     * @var MockObject
     */
    private $filterMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->messageManager = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );

        $this->orderCollectionMock = $this->createMock(Collection::class);
        $this->filterMock = $this->createMock(Filter::class);

        $this->orderCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->orderCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->resultRedirect = $this->createMock(Redirect::class);
        $resultRedirectFactory = $this->createMock(ResultFactory::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
        $this->controller = $objectManagerHelper->getObject(
            Pdfinvoices::class,
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

    /**
     * @throws LocalizedException
     */
    public function testExecute()
    {
        $exception = new \Exception();
        $this->filterMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($this->orderCollectionMock)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->resultRedirect->expects($this->once())->method('setPath')->willReturnSelf();
        $this->controller->execute($exception);
    }
}
