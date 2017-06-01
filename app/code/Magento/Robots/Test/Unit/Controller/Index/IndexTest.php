<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Robots\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Robots\Controller\Index\Index
     */
    private $controller;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRawFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new \Magento\Robots\Controller\Index\Index(
            $this->contextMock,
            $this->resultRawFactoryMock,
            $this->scopeConfigMock
        );
    }

    /**
     * Check the basic flow of execute() method
     */
    public function testExecute()
    {
        $content = 'test';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'design/search_engine_robots/custom_instructions',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn($content);

        $resultRawMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawMock->expects($this->once())
            ->method('setContents')
            ->with($content);

        $this->resultRawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($resultRawMock);

        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Raw::class,
            $this->controller->execute()
        );
    }
}
