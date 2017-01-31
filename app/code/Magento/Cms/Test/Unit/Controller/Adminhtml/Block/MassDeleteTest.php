<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;

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

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\ResourceModel\Block\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->blockCollectionMock =
            $this->getMock('Magento\Cms\Model\ResourceModel\Block\Collection', [], [], '', false);

        $this->massDeleteController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Block\MassDelete',
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
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedBlocksCount));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create Cms Block Collection Mock
     *
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockMock()
    {
        $blockMock = $this->getMock('Magento\Cms\Model\ResourceModel\Block\Collection', ['delete'], [], '', false);
        $blockMock->expects($this->once())->method('delete')->willReturn(true);

        return $blockMock;
    }
}
