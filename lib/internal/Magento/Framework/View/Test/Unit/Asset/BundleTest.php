<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Asset\Bundle\ConfigInterface;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Minification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Framework\View\Asset\Bundle
 */
class BundleTest extends TestCase
{
    /**
     * @var Bundle
     */
    protected $bundle;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $bundleConfigMock;

    /**
     * @var Minification|MockObject
     */
    protected $minificationMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->minificationMock = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundle = new Bundle(
            $this->filesystemMock,
            $this->bundleConfigMock,
            $this->minificationMock
        );
    }

    /**
     * @return void
     * @covers \Magento\Framework\View\Asset\Bundle::getAssetKey
     * @covers \Magento\Framework\View\Asset\Bundle::save
     */
    public function testMinSuffix()
    {
        $this->minificationMock
            ->expects($this->any())
            ->method('addMinifiedSign')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['onefile.js'] => 'onefile.min.js',
                ['path-to-theme/js/bundle/bundle0.js'] => 'path-to-theme/js/bundle/bundle0.min.js'
            });
        $contextMock = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock
            ->expects($this->any())
            ->method('getAreaCode')
            ->willReturn('area');
        $contextMock
            ->expects($this->any())
            ->method('getThemePath')
            ->willReturn('theme-path');
        $contextMock
            ->expects($this->any())
            ->method('getLocale')
            ->willReturn('locale');
        $contextMock
            ->expects($this->any())
            ->method('getPath')
            ->willReturn('path-to-theme');

        $assetMock = $this->getMockBuilder(LocalInterface::class)
            ->onlyMethods(['getContentType', 'getContext'])
            ->getMockForAbstractClass();
        $assetMock->method('getContext')
            ->willReturn($contextMock);
        $assetMock->method('getContentType')
            ->willReturn('js');
        $assetMock->method('getFilePath')
            ->willReturn('onefile.js');
        $assetMock->method('getContent')
            ->willReturn('');   // PHP 8.1 compatibility

        $writeMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $writeMock
            ->expects($this->once())
            ->method('delete')
            ->with('path-to-theme' . DIRECTORY_SEPARATOR . Manager::BUNDLE_JS_DIR);
        $writeMock
            ->expects($this->once())
            ->method('writeFile')
            ->with('path-to-theme/js/bundle/bundle0.min.js', $this->stringContains('onefile.min.js'));

        $this->filesystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($writeMock);

        $this->bundle->addAsset($assetMock);
        $this->bundle->flush();
    }
}
