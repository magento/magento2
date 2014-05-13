<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Block\Html;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Theme\Block\Html\Header::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false);
        $mediaDirectory = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
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
            array('checkDbUsage'),
            array(),
            '',
            false,
            false
        );
        $helper->expects($this->once())->method('checkDbUsage')->will($this->returnValue(false));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $arguments = array(
            'scopeConfig' => $scopeConfig,
            'urlBuilder' => $urlBuilder,
            'fileStorageHelper' => $helper,
            'filesystem' => $filesystem
        );
        $block = $objectManager->getObject('Magento\Theme\Block\Html\Header', $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }
}
