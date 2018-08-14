<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

class CleanStaticFilesTest extends \PHPUnit\Framework\TestCase
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
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'objectManager' => $this->objectManagerMock,
                'eventManager' => $this->eventManagerMock,
                'messageManager' => $this->messageManagerMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );

        $this->controller = $objectHelper->getObject(
            \Magento\Backend\Controller\Adminhtml\Cache\CleanStaticFiles::class,
            ['context' => $context]
        );
    }

    public function testExecute()
    {
        $cleanupFilesMock = $this->getMockBuilder(\Magento\Framework\App\State\CleanupFiles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cleanupFilesMock->expects($this->once())
            ->method('clearMaterializedViewFiles');
        $this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($cleanupFilesMock));

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_static_files_cache_after');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The static files cache has been cleaned.');

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
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
