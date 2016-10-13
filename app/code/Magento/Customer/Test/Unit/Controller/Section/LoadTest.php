<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Section;

use Magento\Customer\Controller\Section\Load;

/**
 * Test customer section load controller
 */
class LoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Load
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\CustomerData\Section\Identifier | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionIdentifier;

    /**
     * @var \Magento\Customer\CustomerData\SectionPoolInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionPool;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->sectionIdentifier = $this->getMockBuilder(\Magento\Customer\CustomerData\Section\Identifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sectionPool = $this->getMockBuilder(\Magento\Customer\CustomerData\SectionPoolInterface::class)
            ->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $this->objectManager->getObject(
            'Magento\Customer\Controller\Section\Load',
            [
                'resultJsonFactory' => $this->resultJsonFactory,
                'sectionIdentifier' => $this->sectionIdentifier,
                'sectionPool' => $this->sectionPool,
                'request' => $this->request
            ]
        );
    }

    public function testExecuteBadSectionData()
    {
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('sections')
            ->willReturn('badSectionData');

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('update_section_id')
            ->willReturn(false);

        $message = '&quot;badSectionData&quot; section source is not supported';
        $expected = ['message' => $message];
        $this->sectionPool->expects($this->once())
            ->method('getSectionsData')
            ->will($this->throwException(new \Exception));

        $escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturn($message);

        $reflection = new \ReflectionClass(get_class($this->controller));
        $reflectionProperty = $reflection->getProperty('escaper');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->controller, $escaper);

        $resultJson->expects($this->once())
            ->method('setData')
            ->with($expected)
            ->willReturn(json_encode($expected));
        $this->assertSame(json_encode($expected), $this->controller->execute());
    }

    public function testExecuteSuccess()
    {
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('sections')
            ->willReturn('cart');

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('update_section_id')
            ->willReturn(false);

        $expected = ['section' => 'cart'];
        $this->sectionPool->expects($this->once())
            ->method('getSectionsData')
            ->willReturn($expected);

        $resultJson->expects($this->once())
            ->method('setData')
            ->with($expected)
            ->willReturn(json_encode($expected));
        $this->assertSame(json_encode($expected), $this->controller->execute());
    }
}
