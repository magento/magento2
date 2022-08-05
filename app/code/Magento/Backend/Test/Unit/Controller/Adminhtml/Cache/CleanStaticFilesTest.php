<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Cache\CleanStaticFiles;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanStaticFilesTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var  ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var CleanStaticFiles
     */
    private $controller;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectHelper = new ObjectManager($this);
        $context = $objectHelper->getObject(
            Context::class,
            [
                'objectManager' => $this->objectManagerMock,
                'eventManager' => $this->eventManagerMock,
                'messageManager' => $this->messageManagerMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );

        $this->controller = $objectHelper->getObject(
            CleanStaticFiles::class,
            ['context' => $context]
        );
    }

    public function testExecute()
    {
        $cleanupFilesMock = $this->getMockBuilder(CleanupFiles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cleanupFilesMock->expects($this->once())
            ->method('clearMaterializedViewFiles');
        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($cleanupFilesMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_static_files_cache_after');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The static files cache has been cleaned.');

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        // Run
        $this->controller->execute();
    }
}
