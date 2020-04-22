<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Controller\Adminhtml\Block\MassDelete;
use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class MassDeleteTest extends AbstractMassActionTest
{
    /**
     * @var MassDelete
     */
    protected $massDeleteController;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $blockCollectionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->blockCollectionMock =
            $this->createMock(Collection::class);

        $this->massDeleteController = $this->objectManager->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testMassDeleteAction()
    {
        $deletedBlocksCount = 2;

        $collection = [
            $this->getBlockMock(),
            $this->getBlockMock()
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

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedBlocksCount));
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create Cms Block Mock
     *
     * @return BlockInterface|MockObject
     */
    protected function getBlockMock()
    {
        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['delete'])
            ->getMockForAbstractClass();
        $blockMock->expects($this->once())->method('delete')->willReturn(true);

        return $blockMock;
    }
}
