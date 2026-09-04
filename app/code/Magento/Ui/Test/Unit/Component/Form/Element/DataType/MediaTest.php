<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\DataType\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    protected $context;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var Processor|MockObject */
    protected $processor;

    /** @var Media */
    protected $media;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->media = new Media($this->context);
    }

    public function testPrepareWithoutDataScope()
    {
        $this->media->setData(
            [
                'name' => 'test_name',
                'config' => [
                    'uploaderConfig' => [
                        'url' => 'module/actionPath/path'
                    ],
                ],
            ]
        );
        $url = 'http://magento2.com/module/actionPath/path/key/34523456234523trdg';
        $expectedConfig = [
            'uploaderConfig' => ['url' => $url],
            'dataScope' => 'test_name'
        ];

        $this->processor = $this->createMock(Processor::class);
        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processor);
        $this->context->expects($this->once())
            ->method('getUrl')
            ->with('module/actionPath/path', ['_secure' => true])
            ->willReturn($url);
        $this->media->prepare();
        $configuration = $this->media->getConfiguration();
        $this->assertEquals($expectedConfig, $configuration);
    }

    public function testPrepareWithDataScope()
    {
        $this->media->setData(
            [
                'name' => 'test_name',
                'config' => [
                    'dataScope' => 'other_data_scope',
                    'uploaderConfig' => [
                        'url' => 'module/actionPath/path'
                    ],
                ],
            ]
        );
        $url = 'http://magento2.com/module/actionPath/path/key/34523456234523trdg';
        $expectedConfig = [
            'uploaderConfig' => ['url' => $url],
            'dataScope' => 'other_data_scope'
        ];

        $this->processor = $this->createMock(Processor::class);
        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processor);
        $this->context->expects($this->once())
            ->method('getUrl')
            ->with('module/actionPath/path', ['_secure' => true])
            ->willReturn($url);
        $this->media->prepare();
        $configuration = $this->media->getConfiguration();
        $this->assertEquals($expectedConfig, $configuration);
    }
}
