<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Robots\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Robots\Controller\Index\Index
     */
    private $controller;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->controller = new \Magento\Robots\Controller\Index\Index(
            $this->contextMock,
            $this->resultPageFactory
        );
    }

    /**
     * Check the basic flow of execute() method
     */
    public function testExecute()
    {
        $resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageMock->expects($this->once())
            ->method('addHandle')
            ->with('robots_index_index');
        $resultPageMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'text/plain');

        $this->resultPageFactory->expects($this->any())
            ->method('create')
            ->with(true)
            ->willReturn($resultPageMock);

        $this->assertInstanceOf(
            \Magento\Framework\View\Result\Page::class,
            $this->controller->execute()
        );
    }
}
