<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\Vertical;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class VerticalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Vertical
     */
    private $vertical;

    /**
     * @var AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractElementMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Form|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formMock;

    protected function setUp()
    {
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment', 'getLabel', 'getHint'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(\Magento\Framework\Escaper::class);
        $reflection = new \ReflectionClass($this->abstractElementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->abstractElementMock, $escaper);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->vertical = $objectManager->getObject(
            Vertical::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testRender()
    {
        $this->abstractElementMock->setForm($this->formMock);
        $this->abstractElementMock->expects($this->any())
            ->method('getComment')
            ->willReturn('New comment');
        $this->abstractElementMock->expects($this->any())
            ->method('getHint')
            ->willReturn('New hint');
        $html = $this->vertical->render($this->abstractElementMock);
        $this->assertRegExp(
            "/New comment/",
            $html
        );
        $this->assertRegExp(
            "/New hint/",
            $html
        );
    }
}
