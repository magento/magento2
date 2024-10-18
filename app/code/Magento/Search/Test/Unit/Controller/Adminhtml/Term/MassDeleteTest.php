<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Controller\Adminhtml\Term\MassDelete;
use Magento\Search\Model\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassDeleteTest extends TestCase
{
    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var MassDelete
     */
    private $controller;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var PageFactory|MockObject
     */
    private $pageFactory;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMockForAbstractClass();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addSuccessMessage', 'addErrorMessage'])
            ->getMockForAbstractClass();
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->addMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            MassDelete::class,
            [
                'context' => $this->context,
                'resultPageFactory' => $this->pageFactory
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $ids = [1, 2];
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('search')
            ->willReturn($ids);

        $willReturnArgs = [];

        $query = $this->createQuery(1);
        $willReturnArgs[] = $query;

        $query = $this->createQuery(2);
        $willReturnArgs[] = $query;

        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg) use ($willReturnArgs) {
                if ($arg == Query::class) {
                    static $callCount = 0;
                    $returnValue = $willReturnArgs[$callCount] ?? null;
                    $callCount++;
                    return $returnValue;
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')->willReturnSelf();
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('search/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }

    /**
     * @param $id
     *
     * @return Query|MockObject
     */
    private function createQuery($id): MockObject
    {
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'delete'])
            ->getMock();
        $query
            ->method('delete')
            ->willReturn($query);
        $query
            ->method('load')
            ->with($id)
            ->willReturn($query);

        return $query;
    }
}
