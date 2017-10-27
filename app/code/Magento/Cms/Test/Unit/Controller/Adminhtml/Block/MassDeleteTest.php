<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDeleteTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Block
 */
class MassDeleteTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\MassDelete
     */
    protected $massDeleteController;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockCollectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Block\CollectionFactory::class,
            ['create']
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->blockCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Block\Collection::class);
        $this->blockRepository = $this->getMockBuilder(\Magento\Cms\Api\BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->massDeleteController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Block\MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'blockRepository' => $this->blockRepository,
            ]
        );
    }

    public function testMassDeleteAction()
    {
        $deletedBlocksCount = 2;

        $collection = [
            $this->getBlockMock('Title 1'),
            $this->getBlockMock('Title 2'),
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->blockCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->blockCollectionMock)
            ->willReturn($this->blockCollectionMock);

        $this->blockCollectionMock->expects($this->once())->method('getSize')->willReturn($deletedBlocksCount);
        $this->blockCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->blockRepository->expects($this->exactly($deletedBlocksCount))
            ->method('delete')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedBlocksCount));
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
        $deletedBlocksCount = 2;

        $block1 = $this->getBlockMock('Title 1');
        $block2 = $this->getBlockMock('Title 2');
        $collection = [
            $block1,
            $block2,
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->blockCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->blockCollectionMock)
            ->willReturn($this->blockCollectionMock);

        $this->blockCollectionMock->expects($this->once())->method('getSize')->willReturn($deletedBlocksCount);
        $this->blockCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->with($block2)
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
     * Create Cms Block Collection Mock
     *
     * @param $title
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockMock($title)
    {
        $blockMock = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'delete', 'getTitle', 'getId'])
            ->getMock();

        $blockMock->expects($this->never())
            ->method('load');
        $blockMock->expects($this->never())
            ->method('delete');
        $blockMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $blockMock->expects($this->never())
            ->method('getId');

        return $blockMock;
    }

    /**
     * @return array
     */
    public function deleteActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while deleting the block.')),
                __('Something went wrong while deleting the block.'),
            ],
            'CouldNotDeleteException' => [
                new LocalizedException(__('Could not delete.')),
                null
            ],
        ];
    }
}
