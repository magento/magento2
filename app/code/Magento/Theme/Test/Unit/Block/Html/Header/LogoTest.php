<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html\Header;

use Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Theme\Block\Html\Header\Logo;
use PHPUnit\Framework\TestCase;

class LogoTest extends TestCase
{
    /**
     * cover \Magento\Theme\Block\Html\Header\Logo::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $mediaDirectory = $this->createMock(Read::class);
        $logoPathResolver = $this->createMock(LogoPathResolverInterface::class);

        $urlBuilder = $this->createMock(UrlInterface::class);

        $logoPathResolver->expects($this->once())->method('getPath')->willReturn('logo/default/image.gif');
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getBaseUrl'
        )->willReturn(
            'http://localhost/media/'
        );
        $mediaDirectory->expects($this->any())->method('isFile')->willReturn(true);

        $filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($mediaDirectory);
        $helper = $this->createPartialMock(Database::class, ['checkDbUsage']);
        $helper->expects($this->once())->method('checkDbUsage')->willReturn(false);

        $objectManager = new ObjectManager($this);

        $arguments = [
            'data' => ['logoPathResolver' => $logoPathResolver],
            'urlBuilder' => $urlBuilder,
            'fileStorageHelper' => $helper,
            'filesystem' => $filesystem,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals('http://localhost/media/logo/default/image.gif', $block->getLogoSrc());
    }

    /**
     * cover \Magento\Theme\Block\Html\Header\Logo::getLogoHeight
     */
    public function testGetLogoHeight()
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())->method('getValue')->willReturn(null);

        $objectManager = new ObjectManager($this);
        $arguments = [
            'scopeConfig' => $scopeConfig,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals(0, $block->getLogoHeight());
    }

    /**
     * @covers \Magento\Theme\Block\Html\Header\Logo::getLogoWidth
     */
    public function testGetLogoWidth()
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())->method('getValue')->willReturn('170');

        $objectManager = new ObjectManager($this);
        $arguments = [
            'scopeConfig' => $scopeConfig,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals('170', $block->getLogoHeight());
    }
}
