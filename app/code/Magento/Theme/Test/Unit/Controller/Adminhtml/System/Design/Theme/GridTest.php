<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Theme\Controller\Adminhtml\System\Design\Theme\Grid;

class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repository;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $view;

    /**
     * @var Delete
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->getMock();
        $context->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);

        $this->registry = $this->getMockBuilder(
            \Magento\Framework\Registry::class
        )->disableOriginalConstructor()->getMock();
        $this->fileFactory = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Backend\App\Action\Context $context */
        $this->controller = new Grid(
            $context,
            $this->registry,
            $this->fileFactory,
            $this->repository,
            $this->filesystem
        );
    }

    public function testExecute()
    {
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->with(false);
        $this->view->expects($this->once())
            ->method('renderLayout');
        $this->controller->execute();
    }
}
