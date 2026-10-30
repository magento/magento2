<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryRenditions\Model\Config;
use Magento\MediaGalleryRenditions\Model\GenerateRenditions;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GenerateRenditionsTest extends TestCase
{
    /**
     * @var GenerateRenditions
     */
    private $model;

    /**
     * @var AdapterFactory|MockObject
     */
    private $imageFactoryMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var GetRenditionPathInterface|MockObject
     */
    private $getRenditionPathMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var File|MockObject
     */
    private $driverMock;

    /**
     * @var IsPathExcludedInterface|MockObject
     */
    private $isPathExcludedMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->imageFactoryMock = $this->createMock(AdapterFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->getRenditionPathMock = $this->createMock(GetRenditionPathInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->driverMock = $this->createMock(File::class);
        $this->isPathExcludedMock = $this->createMock(IsPathExcludedInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->model = new GenerateRenditions(
            $this->imageFactoryMock,
            $this->configMock,
            $this->getRenditionPathMock,
            $this->filesystemMock,
            $this->driverMock,
            $this->isPathExcludedMock,
            $this->loggerMock
        );
    }

    /**
     * Test getImageFileNamePattern method returns correct regex pattern
     */
    public function testGetImageFileNamePattern(): void
    {
        $pattern = $this->model->getImageFileNamePattern();
        
        // Assert the pattern is the expected string
        $this->assertEquals('#\.(jpg|jpeg|gif|png)$# i', $pattern);
        
        // Test that the pattern correctly validates supported file types
        $validExtensions = ['test.jpg', 'test.jpeg', 'test.gif', 'test.png', 'TEST.JPG', 'TEST.PNG'];
        foreach ($validExtensions as $filename) {
            $this->assertEquals(
                1,
                preg_match($pattern, $filename),
                "Pattern should match valid image file: $filename"
            );
        }
        
        // Test that the pattern correctly rejects unsupported file types
        $invalidExtensions = ['test.txt', 'test.pdf', 'test.webp', 'test.bmp', 'test'];
        foreach ($invalidExtensions as $filename) {
            $this->assertEquals(
                0,
                preg_match($pattern, $filename),
                "Pattern should not match invalid image file: $filename"
            );
        }
    }

    /**
     * Test that pattern is case-insensitive
     */
    public function testGetImageFileNamePatternCaseInsensitive(): void
    {
        $pattern = $this->model->getImageFileNamePattern();
        $mixedCaseFiles = [
            'image.JPG',
            'image.Jpg',
            'image.JPEG',
            'image.Jpeg',
            'image.GIF',
            'image.Gif',
            'image.PNG',
            'image.Png'
        ];
        
        foreach ($mixedCaseFiles as $filename) {
            $this->assertEquals(
                1,
                preg_match($pattern, $filename),
                "Pattern should match case-insensitive image file: $filename"
            );
        }
    }
}
