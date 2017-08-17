<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Html\Header;

class LogoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * cover \Magento\Theme\Block\Html\Header\Logo::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $mediaDirectory = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);

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
        $helper = $this->createPartialMock(\Magento\MediaStorage\Helper\File\Storage\Database::class, ['checkDbUsage']);
        $helper->expects($this->once())->method('checkDbUsage')->will($this->returnValue(false));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $arguments = [
            'scopeConfig' => $scopeConfig,
            'urlBuilder' => $urlBuilder,
            'fileStorageHelper' => $helper,
            'filesystem' => $filesystem,
        ];
        $block = $objectManager->getObject(\Magento\Theme\Block\Html\Header\Logo::class, $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }
}
