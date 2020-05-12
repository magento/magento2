<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\ExportButton;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportButtonTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ExportButton
     */
    protected $model;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = $this->objectManager->getObject(
            ExportButton::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'context' => $this->context,
            ]
        );
    }

    public function testGetComponentName()
    {
        $this->context->expects($this->never())->method('getProcessor');
        $this->assertEquals(ExportButton::NAME, $this->model->getComponentName());
    }

    public function testPrepare()
    {
        $processor = $this->getMockBuilder(Processor::class)
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
