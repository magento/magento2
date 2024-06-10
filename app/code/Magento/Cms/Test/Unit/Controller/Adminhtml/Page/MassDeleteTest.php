<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Controller\Adminhtml\Page\MassDelete;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MassDeleteTest extends AbstractMassActionTestCase
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
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection|MockObject
     */
    protected $pageCollectionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->pageCollectionMock =
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
        $deletedPagesCount = 2;

        $collection = [
            $this->getPageMock(),
            $this->getPageMock()
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

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedPagesCount));
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create Cms Page Collection Mock
     *
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|MockObject
     */
    protected function getPageMock()
    {
        $pageMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->once())->method('delete')->willReturn(true);

        return $pageMock;
    }
}
