<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;

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

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class,
            ['create']
        );

        $this->pageCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Page\Collection::class);

        $this->massEnableController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\MassEnable::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testMassEnableAction()
    {
        $enabledPagesCount = 2;

        $collection = [
            $this->getPageMock(),
            $this->getPageMock()
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($this->pageCollectionMock);

        $this->pageCollectionMock->expects($this->once())->method('getSize')->willReturn($enabledPagesCount);
        $this->pageCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been enabled.', $enabledPagesCount));
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');

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
        $pageMock = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\Collection::class,
            ['setIsActive', 'save']
        );
        $pageMock->expects($this->once())->method('setIsActive')->with(true)->willReturn(true);
        $pageMock->expects($this->once())->method('save')->willReturn(true);

        return $pageMock;
    }
}
