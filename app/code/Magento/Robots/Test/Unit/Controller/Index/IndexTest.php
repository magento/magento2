<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Controller\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Robots\Controller\Index\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    protected function setUp(): void
    {
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            Index::class,
            [
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * Check the basic flow of execute() method
     */
    public function testExecute()
    {
        $resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageMock->expects($this->once())
            ->method('addHandle')
            ->with('robots_index_index');
        $resultPageMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'text/plain');

        $this->resultPageFactoryMock->method('create')
            ->with(true)
            ->willReturn($resultPageMock);

        $this->assertInstanceOf(
            Page::class,
            $this->controller->execute()
        );
    }
}
