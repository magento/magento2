<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html\Header;

use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\Repository;
use Magento\Theme\Block\Html\Header\CriticalCss;
use PHPUnit\Framework\TestCase;

class CriticalCssTest extends TestCase
{
    private const DEFAULT_PATH = 'css/critical.css';
    private const CUSTOM_PATH = 'css/critical-search.css';

    public function testGetCriticalCssDataUsesConfiguredPath(): void
    {
        $viewModel = new CriticalCss($this->createAssetRepo(), self::DEFAULT_PATH);

        $this->assertSame('content of ' . self::DEFAULT_PATH, $viewModel->getCriticalCssData());
    }

    public function testGetCriticalCssDataByPathUsesGivenPath(): void
    {
        $viewModel = new CriticalCss($this->createAssetRepo(), self::DEFAULT_PATH);

        $this->assertSame('content of ' . self::CUSTOM_PATH, $viewModel->getCriticalCssDataByPath(self::CUSTOM_PATH));
    }

    public function testMissingFileReturnsEmptyString(): void
    {
        $file = $this->createStub(File::class);
        $file->method('getContent')->willThrowException(new NotFoundException('missing'));
        $assetRepo = $this->createStub(Repository::class);
        $assetRepo->method('createAsset')->willReturn($file);

        $viewModel = new CriticalCss($assetRepo, self::DEFAULT_PATH);

        $this->assertSame('', $viewModel->getCriticalCssDataByPath(self::CUSTOM_PATH));
    }

    private function createAssetRepo(): Repository
    {
        $assetRepo = $this->createStub(Repository::class);
        $assetRepo->method('createAsset')->willReturnCallback(function (string $path) {
            $file = $this->createStub(File::class);
            $file->method('getContent')->willReturn('content of ' . $path);
            return $file;
        });

        return $assetRepo;
    }
}
