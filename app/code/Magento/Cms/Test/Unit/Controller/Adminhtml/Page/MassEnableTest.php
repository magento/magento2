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
class MassEnableTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassEnable
     */
    protected $massEnableController;

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
            ->enableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->massEnableController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\MassEnable::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    public function testMassEnableAction()
    {
        $enabledPagesCount = 2;

        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with(true)
            ->willReturn(true);
        $page2->expects($this->once())
            ->method('setIsActive')
            ->with(true)
            ->willReturn(true);

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
            ->with(__('A total of %1 record(s) have been enabled.', $enabledPagesCount));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massEnableController->execute());
    }

    /**
     * @param $exception
     * @param $errorMsg
     * @dataProvider massEnableActionThrowsExceptionDataProvider
     */
    public function testMassEnableActionThrowsException($exception, $errorMsg)
    {
        $page1 = $this->getPageMock();
        $page2 = $this->getPageMock();
        $collection = [
            $page1,
            $page2,
        ];
        $page1->expects($this->once())
            ->method('setIsActive')
            ->with(true)
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

        $this->assertSame($this->resultRedirectMock, $this->massEnableController->execute());
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
    public function massEnableActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while enabling the page.')),
                __('Something went wrong while enabling the page.'),
            ],
            'CouldNotSaveException' => [
                new LocalizedException(__('Could not save.')),
                null
            ],
        ];
    }
}
