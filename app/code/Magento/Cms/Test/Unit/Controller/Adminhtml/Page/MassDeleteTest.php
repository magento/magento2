<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDeleteTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Page
 */
class MassDeleteTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassDelete
     */
    protected $massDeleteController;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class,
            ['create']
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->pageCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Page\Collection::class);
        $this->pageRepository = $this->getMockBuilder(\Magento\Cms\Api\PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->massDeleteController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    public function testMassDeleteAction()
    {
        $deletedPagesCount = 2;

        $collection = [
            $this->getPageMock('Title 1'),
            $this->getPageMock('Title 2'),
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->once())->method('getSize')->willReturn($deletedPagesCount);
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->pageRepository->expects($this->exactly($deletedPagesCount))
            ->method('delete')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedPagesCount));
        $this->messageManagerMock->expects($this->never())->method('addExceptionMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * @param $exception
     * @param $errorMsg
     * @dataProvider deleteActionThrowsExceptionDataProvider
     */
    public function testMassDeleteActionThrowsException($exception, $errorMsg)
    {
        $deletedPagesCount = 2;

        $page1 = $this->getPageMock('Title 1');
        $page2 = $this->getPageMock('Title 2');
        $collection = [
            $page1,
            $page2,
        ];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($deletedPagesCount);
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->pageRepository->expects($this->once())
            ->method('delete')
            ->with($page2)
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

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create Cms Page Collection Mock
     *
     * @param $title
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPageMock($title)
    {
        $pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'delete', 'getTitle', 'getId'])
            ->getMock();

        $pageMock->expects($this->never())
            ->method('load');
        $pageMock->expects($this->never())
            ->method('delete');
        $pageMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $pageMock->expects($this->never())
            ->method('getId');

        return $pageMock;
    }

    /**
     * @return array
     */
    public function deleteActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while deleting the page.')),
                __('Something went wrong while deleting the page.'),
            ],
            'CouldNotDeleteException' => [
                new LocalizedException(__('Could not delete.')),
                null
            ],
        ];
    }
}
