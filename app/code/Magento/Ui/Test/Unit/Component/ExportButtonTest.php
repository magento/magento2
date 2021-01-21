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
class ExportButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
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
        $this->context->expects($this->any())
            ->method('getRequestParam')
            ->with('test_asterisk')
            ->willReturn('test_asterisk_value');
        $option = ['label' => 'test label', 'value' => 'test value', 'url' => 'test_url'];
        $data = [
            'config' => [
                'options' => [
                    $option
                ],
                'additionalParams' => [
                    'test_key' => 'test_value',
                    'test_asterisk' => '*'
                ]
            ],
        ];
        $expected = $data;
        $expected['config']['options'][0]['url'] = [
            'test_key' => 'test_value',
            'test_asterisk' => 'test_asterisk_value',
        ];
        $this->model->setData($data);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('test_url')
            ->willReturnArgument(1);

        self::assertNull($this->model->prepare());
        self::assertEquals(
            $expected,
            $this->model->getData()
        );
    }
}
