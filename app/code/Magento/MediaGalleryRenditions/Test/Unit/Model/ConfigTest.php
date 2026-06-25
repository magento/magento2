<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Unit\Model;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\MediaGalleryRenditions\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Initial|MockObject
     */
    private $initialConfigMock;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->initialConfigMock = $this->createMock(Initial::class);
        $this->config = new Config(
            $this->scopeConfigMock,
            $this->initialConfigMock
        );
    }

    /**
     * Test getWidth() using scopeConfig value
     */
    public function testGetWidthFromScopeConfig(): void
    {
        $expectedWidth = 800;
        $widthPath = 'system/media_gallery_renditions/width';
        $this->scopeConfigMock->method('getValue')
            ->with($widthPath)
            ->willReturn($expectedWidth);
        $result = $this->config->getWidth();
        $this->assertEquals($expectedWidth, $result);
    }

    /**
     * Test getWidth() falling back to initial config
     */
    public function testGetWidthFromInitialConfig(): void
    {
        $expectedWidth = 600;
        $widthPath = 'system/media_gallery_renditions/width';
        $this->scopeConfigMock->method('getValue')
            ->with($widthPath)
            ->willReturn(null);
        $this->initialConfigMock->method('getData')
            ->with('default')
            ->willReturn([
                'system' => [
                    'media_gallery_renditions' => [
                        'width' => $expectedWidth
                    ]
                ]
            ]);
        $result = $this->config->getWidth();
        $this->assertEquals($expectedWidth, $result);
    }

    /**
     * Test getHeight() using scopeConfig value
     */
    public function testGetHeightFromScopeConfig(): void
    {
        $expectedHeight = 600;
        $heightPath = 'system/media_gallery_renditions/height';
        $this->scopeConfigMock->method('getValue')
            ->with($heightPath)
            ->willReturn($expectedHeight);
        $result = $this->config->getHeight();
        $this->assertEquals($expectedHeight, $result);
    }

    /**
     * Test getHeight() falling back to initial config
     */
    public function testGetHeightFromInitialConfig(): void
    {
        $expectedHeight = 400;
        $heightPath = 'system/media_gallery_renditions/height';
        $this->scopeConfigMock->method('getValue')
            ->with($heightPath)
            ->willReturn(null);
        $this->initialConfigMock->method('getData')
            ->with('default')
            ->willReturn([
                'system' => [
                    'media_gallery_renditions' => [
                        'height' => $expectedHeight
                    ]
                ]
            ]);
        $result = $this->config->getHeight();
        $this->assertEquals($expectedHeight, $result);
    }
}
