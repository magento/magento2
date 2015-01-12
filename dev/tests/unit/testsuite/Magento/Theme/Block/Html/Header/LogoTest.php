<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html\Header;

class LogoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Theme\Block\Html\Header\Logo::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $mediaDirectory = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface');

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
        $helper = $this->getMock(
            'Magento\Core\Helper\File\Storage\Database',
            ['checkDbUsage'],
            [],
            '',
            false,
            false
        );
        $helper->expects($this->once())->method('checkDbUsage')->will($this->returnValue(false));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $arguments = [
            'scopeConfig' => $scopeConfig,
            'urlBuilder' => $urlBuilder,
            'fileStorageHelper' => $helper,
            'filesystem' => $filesystem,
        ];
        $block = $objectManager->getObject('Magento\Theme\Block\Html\Header\Logo', $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }
}
