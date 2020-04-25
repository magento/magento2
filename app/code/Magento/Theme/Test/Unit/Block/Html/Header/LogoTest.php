<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $urlBuilder = $this->createMock(UrlInterface::class);

        $scopeConfig->expects($this->once())->method('getValue')->will($this->returnValue('default/image.gif'));
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getBaseUrl'
        )->will(
            $this->returnValue('http://localhost/pub/media/')
        );
        $mediaDirectory->expects($this->any())->method('isFile')->will($this->returnValue(true));

        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($mediaDirectory));
        $helper = $this->createPartialMock(Database::class, ['checkDbUsage']);
        $helper->expects($this->once())->method('checkDbUsage')->will($this->returnValue(false));

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
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())->method('getValue')->willReturn(null);

        $objectManager = new ObjectManager($this);
        $arguments = [
            'scopeConfig' => $scopeConfig,
        ];
        $block = $objectManager->getObject(Logo::class, $arguments);

        $this->assertEquals(null, $block->getLogoHeight());
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
