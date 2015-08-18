<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassDeleteTest;

class MassDeleteTest extends AbstractMassDeleteTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassDelete
     */
    protected $massDeleteController;

    /**
     * @var \Magento\Cms\Model\Resource\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollectionMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $homePageCollectionMock;

    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $welcomePageCollectionMock;

    protected function setUp()
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->pageCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\Collection',
            ['delete'],
            [],
            '',
            false
        );

        $this->homePageCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\Collection',
            ['delete'],
            [],
            '',
            false
        );

        $this->welcomePageCollectionMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\Collection',
            ['delete'],
            [],
            '',
            false
        );

        $this->massDeleteController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Page\MassDelete',
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testMassDeleteAction()
    {
        $deletedPages = 2;

        $collection = [
            $this->homePageCollectionMock,
            $this->welcomePageCollectionMock
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->pageCollectionMock)
            ->willReturn($collection);

        $this->homePageCollectionMock->expects($this->exactly(1))->method('delete')->willReturn(true);
        $this->welcomePageCollectionMock->expects($this->exactly(1))->method('delete')->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) have been deleted.', $deletedPages));
        $this->messageManagerMock->expects($this->never())->method('addError');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }
}
