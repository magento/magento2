<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

class CleanStaticFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var  \Magento\Framework\Event\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Backend\Controller\Adminhtml\Cache\CleanStaticFiles
     */
    private $controller;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            '\Magento\Backend\App\Action\Context',
            [
                'objectManager' => $this->objectManagerMock,
                'eventManager' => $this->eventManagerMock,
                'messageManager' => $this->messageManagerMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );

        $this->controller = $objectHelper->getObject(
            'Magento\Backend\Controller\Adminhtml\Cache\CleanStaticFiles',
            ['context' => $context,]
        );
    }

    public function testExecute()
    {
        $cleanupFilesMock = $this->getMockBuilder('Magento\Framework\App\State\CleanupFiles')
            ->disableOriginalConstructor()
            ->getMock();
        $cleanupFilesMock->expects($this->once())
            ->method('clearMaterializedViewFiles');
        $this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($cleanupFilesMock));

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_static_files_cache_after');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('The static files cache has been cleaned.');

        $resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        // Run
        $this->controller->execute();
    }
}
