<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassDeleteTest;

class MassDeleteTest extends AbstractMassDeleteTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\MassDelete
     */
    protected $massDeleteController;

    /**
     * @var \Magento\Cms\Model\Resource\Block\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Cms\Model\Resource\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockCollectionMock;

    /**
     * @var \Magento\Cms\Model\Resource\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $headerBlockCollectionMock;

    /**
     * @var \Magento\Cms\Model\Resource\Block\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $footerBlockCollectionMock;

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\Resource\Block\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->blockCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Block\Collection',
            ['delete'],
            [],
            '',
            false
        );

        $this->headerBlockCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Block\Collection',
            ['delete'],
            [],
            '',
            false
        );

        $this->footerBlockCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Block\Collection',
            ['delete'],
            [],
            '',
            false
        );

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
        $deletedBlocks = 2;

        $collection = [
            $this->headerBlockCollectionMock,
            $this->footerBlockCollectionMock
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->blockCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->blockCollectionMock)
            ->willReturn($collection);

        $this->headerBlockCollectionMock->expects($this->exactly(1))->method('delete')->willReturn(true);
        $this->footerBlockCollectionMock->expects($this->exactly(1))->method('delete')->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedBlocks));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }
}
