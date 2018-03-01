<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDisableTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Page
 */
class MassDisableTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassDisable
     */
    protected $massDisableController;

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

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class,
            ['create']
        );

        $this->pageCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Page\Collection::class);
        $this->pageRepository = $this->getMockBuilder(\Magento\Cms\Api\PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->massDisableController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\MassDisable::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    public function testMassDisableAction()
    {
        $disabledPagesCount = 2;

        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with(false)
            ->willReturn(true);
        $page2->expects($this->once())
            ->method('setIsActive')
            ->with(false)
            ->willReturn(true);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->once())->method('getSize')->willReturn($disabledPagesCount);
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->pageRepository->expects($this->exactly($disabledPagesCount))
            ->method('save')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been disabled.', $disabledPagesCount));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDisableController->execute());
    }

    /**
     * @param $exception
     * @param $errorMsg
     * @dataProvider massDisableActionThrowsExceptionDataProvider
     */
    public function testMassDisableActionThrowsException($exception, $errorMsg)
    {
        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with(false)
            ->willReturn(true);
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

        $this->assertSame($this->resultRedirectMock, $this->massDisableController->execute());
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
    public function massDisableActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while disabling the page.')),
                __('Something went wrong while disabling the page.'),
            ],
            'CouldNotSaveException' => [
                new LocalizedException(__('Could not save.')),
                null
            ],
        ];
    }
}
