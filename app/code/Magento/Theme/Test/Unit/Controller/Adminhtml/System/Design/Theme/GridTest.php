<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\Repository;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme\Grid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactory;

    /**
     * @var Repository|MockObject
     */
    protected $repository;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var ViewInterface|MockObject
     */
    protected $view;

    /**
     * @var Delete
     */
    protected $controller;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->getMock();
        $context->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);

        $this->registry = $this->getMockBuilder(
            Registry::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Context $context */
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
