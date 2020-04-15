<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Type\System;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Config\App\Config\Type\System\Reader;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $source;

    /**
     * @var Fallback|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fallback;

    /**
     * @var PreProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $preProcessor;

    /**
     * @var PostProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $postProcessor;

    /*
     * Reader
     */
    private $model;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fallback = $this->getMockBuilder(Fallback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->preProcessor = $this->getMockBuilder(PreProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->postProcessor = $this->getMockBuilder(PostProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $helper->getObject(
            Reader::class,
            [
                'source' => $this->source,
                'fallback' => $this->fallback,
                'preProcessor' => $this->preProcessor,
                'postProcessor' => $this->postProcessor
            ]
        );
    }

    public function testGetCachedWithLoadDefaultScopeData()
    {
        $data = [
            'default' => [],
            'websites' => [],
            'stores' => []
        ];
        $this->source->expects($this->once())
            ->method('get')
            ->willReturn($data);
        $this->preProcessor->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturn($data);
        $this->fallback->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturn($data);
        $this->assertEquals($data, $this->model->read());
    }
}
