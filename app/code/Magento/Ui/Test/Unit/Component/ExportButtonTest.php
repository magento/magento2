<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ExportButtonTest
 */
class ExportButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Ui\Component\ExportButton
     */
    protected $model;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            \Magento\Ui\Component\ExportButton::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'context' => $this->context,
            ]
        );
    }

    public function testGetComponentName()
    {
        $this->context->expects($this->never())->method('getProcessor');
        $this->assertEquals(\Magento\Ui\Component\ExportButton::NAME, $this->model->getComponentName());
    }

    public function testPrepare()
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $option = ['label' => 'test label', 'value' => 'test value', 'url' => 'test_url'];
        $data = ['config' => ['options' => [$option]]];
        $this->model->setData($data);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('test_url')
            ->willReturnArgument(0);
        $this->assertNull($this->model->prepare());
    }
}
