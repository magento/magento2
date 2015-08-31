<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Cms\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;

class MassDisableTest extends AbstractMassActionTest
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\MassDisable
     */
    protected $massDisableController;

    /**
     * @var \Magento\Cms\Model\Resource\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

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

        $this->massDisableController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Page\MassDisable',
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testMassDisableAction()
    {
        $size = 2;
        $message = 'A total of %1 record(s) have been disabled.';

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->pageCollectionMock);

        $this->processMassAction($message, $size);

        $this->assertSame($this->resultRedirectMock, $this->massDisableController->execute());
    }

    /**
     * Create Cms Page Collection Mock
     *
     * @return \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPageMock()
    {
        $pageMock = $this->getMock(
            'Magento\Cms\Model\Resource\Page\Collection',
            ['setIsActive', 'save'],
            [],
            '',
            false
        );
        $pageMock->expects($this->once())->method('setIsActive')->with(false)->willReturn(true);
        $pageMock->expects($this->once())->method('save')->willReturn(true);

        return $pageMock;
    }
}
