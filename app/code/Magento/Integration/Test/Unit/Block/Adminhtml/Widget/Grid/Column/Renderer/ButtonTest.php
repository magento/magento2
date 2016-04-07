<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class ButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button
     */
    protected $buttonRenderer;

    protected function setUp()
    {
        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->contextMock = $this->getMock('Magento\Backend\Block\Context', ['getEscaper'], [], '', false);
        $this->contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($this->escaperMock));

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->buttonRenderer = $this->objectManagerHelper->getObject(
            'Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button',
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $expectedResult = '<button id="1" type="bigButton">my button</button>';
        $column = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getId', 'getIndex'])
            ->getMock();
        $column->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bigButton'));
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $column->expects($this->any())
            ->method('getIndex')
            ->willReturn('name');
        $this->buttonRenderer->setColumn($column);

        $object = new \Magento\Framework\DataObject(['name' => 'my button']);
        $actualResult = $this->buttonRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
