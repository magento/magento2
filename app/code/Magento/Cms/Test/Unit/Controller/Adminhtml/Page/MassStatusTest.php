<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassEnableTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Page
 */
class MassStatusTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassStatus
     */
    protected $massStatusController;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollectionMock;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class,
            ['create']
        );

        $this->pageCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Page\Collection::class);
        $this->pageRepository = $this->getMockBuilder(\Magento\Cms\Api\PageRepositoryInterface::class)
            ->enableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->contextMock
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->massStatusController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\MassStatus::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    /**
     * @param $statusParam
     * @param $statusBool
     * @param $statusLabel
     * @dataProvider massStatusActionDataProvider
     */
    public function testMassStatusAction($statusParam, $statusBool, $statusLabel)
    {
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->with('status')
            ->willReturn($statusParam);

        $enabledPagesCount = 2;

        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with($statusBool)
            ->willReturnSelf();
        $page2->expects($this->once())
            ->method('setIsActive')
            ->with($statusBool)
            ->willReturnSelf();

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->once())->method('getSize')->willReturn($enabledPagesCount);
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->pageRepository->expects($this->exactly($enabledPagesCount))
            ->method('save')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have changed their status to %2.', $enabledPagesCount, $statusLabel));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massStatusController->execute());
    }

    /**
     * @param $exception
     * @param $errorMsg
     * @dataProvider massStatusActionThrowsExceptionDataProvider
     */
    public function testMassStatusActionThrowsException($exception, $errorMsg)
    {
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->with('status')
            ->willReturn(true);

        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with(true)
            ->willReturnSelf();
        $page2->expects($this->never())
            ->method('setIsActive');

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->never())->method('getSize');
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($page1)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massStatusController->execute());
    }

    /**
     * Create Cms Page Collection Mock
     *
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPageMock()
    {
        $pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'delete', 'getId', 'setIsActive', 'save'])
            ->getMock();
        $pageMock->expects($this->never())
            ->method('load');
        $pageMock->expects($this->never())
            ->method('delete');
        $pageMock->expects($this->never())
            ->method('getId');
        $pageMock->expects($this->never())
            ->method('save');

        return $pageMock;
    }

    /**
     * @return array
     */
    public function massStatusActionDataProvider()
    {
        return [
            'massEnable' => ['1', true, __('Enabled')],
            'massDisable' => ['0', false, __('Disabled')],
        ];
    }

    /**
     * @return array
     */
    public function massStatusActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while enabling the page.')),
                __('Something went wrong while changing the page status.'),
            ],
            'CouldNotSaveException' => [
                new LocalizedException(__('Could not save.')),
                null
            ],
        ];
    }
}
