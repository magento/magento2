<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\ViewModel;

use Magento\ConfigurableProduct\ViewModel\UploadResizeConfigValue;
use Magento\Backend\Model\Image\UploadResizeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UploadResizeConfigValueTest extends TestCase
{
    /**
     * @var UploadResizeConfigInterface|MockObject
     */
    private $uploadResizeConfigMock;

    /**
     * @var UploadResizeConfigValue
     */
    private $viewModel;

    protected function setUp(): void
    {
        $this->uploadResizeConfigMock = $this->createMock(UploadResizeConfigInterface::class);
        $this->viewModel = new UploadResizeConfigValue($this->uploadResizeConfigMock);
    }

    public function testGetMaxWidth()
    {
        $this->uploadResizeConfigMock->method('getMaxWidth')->willReturn(100);
        $this->assertEquals(100, $this->viewModel->getMaxWidth());
    }

    public function testGetMaxHeight()
    {
        $this->uploadResizeConfigMock->method('getMaxHeight')->willReturn(200);
        $this->assertEquals(200, $this->viewModel->getMaxHeight());
    }

    public function testIsResizeEnabled()
    {
        $this->uploadResizeConfigMock->method('isResizeEnabled')->willReturn(true);
        $this->assertTrue($this->viewModel->isResizeEnabled());
    }
}
