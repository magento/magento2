<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html\Header;

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
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);

        $scopeConfig->expects($this->once())->method('getValue')->willReturn('default/image.gif');
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getBaseUrl'
        )->willReturn(
            'http://localhost/pub/media/'
        );
        $mediaDirectory->expects($this->any())->method('isFile')->willReturn(true);

        $filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($mediaDirectory);
        $helper = $this->createPartialMock(Database::class, ['checkDbUsage']);
        $helper->expects($this->once())->method('checkDbUsage')->willReturn(false);

        $objectManager = new ObjectManager($this);

        $arguments = [
            'scopeConfig' => $scopeConfig,
            'urlBuilder' => $urlBuilder,
            'fileStorageHelper' => $helper,
            'filesystem' => $filesystem,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }

    /**
     * cover \Magento\Theme\Block\Html\Header\Logo::getLogoHeight
     */
    public function testGetLogoHeight()
    {
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
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
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())->method('getValue')->willReturn('170');

        $objectManager = new ObjectManager($this);
        $arguments = [
            'scopeConfig' => $scopeConfig,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals('170', $block->getLogoHeight());
    }
}
