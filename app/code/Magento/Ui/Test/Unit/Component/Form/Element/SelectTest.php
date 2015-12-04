<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Ui\Component\Form\Element\Select */
    protected $model;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\View\Element\UiComponent\Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiProcessor;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->uiProcessor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->exactly(2))
            ->method('getProcessor')
            ->willReturn($this->uiProcessor);
        $this->uiProcessor->expects($this->once())
            ->method('register');
        $this->model = new \Magento\Ui\Component\Form\Element\Select(
            $this->contextMock,
            [
                [
                    'value' => 1,
                    'label' => 'label2'
                ],
                [
                    'value' => 2,
                    'label' => 'label2'
                ]
            ]
        );
        $this->model->setData('name', 'name');
        $this->model->setData('buttons', []);
    }

    public function testPrepare()
    {
        $this->contextMock->expects($this->once())
            ->method('getNamespace')
            ->willReturn('namespace');
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with('select', ['extends' => 'namespace']);
        $this->contextMock->expects($this->once())
            ->method('addButtons')
            ->with([], $this->model);
        $this->uiProcessor->expects($this->once())
            ->method('notify')
            ->with('select');
        $this->model->prepare();
        $this->assertEquals(
            [
                [
                    'value' => '1',
                    'label' => 'label2'
                ],
                [
                    'value' => '2',
                    'label' => 'label2'
                ]
            ],
            $this->model->getData('config/options')
        );
    }
}
