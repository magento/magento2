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
 * @category    Magento
 * @package     Magento_Page
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Page\Block\Html;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Page\Block\Html\Header::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $storeConfig = $this->getMock('Magento\Core\Model\Store\Config', array('getConfig'), array(), '', false);
        $storeConfig->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue('default/image.gif'));

        $urlBuilder = $this->getMock('Magento\UrlInterface');
        $urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://localhost/pub/media/'));

        $helper = $this->getMock('Magento\Core\Helper\File\Storage\Database',
            array('checkDbUsage'), array(), '', false, false
        );
        $helper->expects($this->once())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));

        $helperFactory = $this->getMock('Magento\Core\Model\Factory\Helper', array('get'), array(), '', false);
        $helperFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($helper));

        $dirsMock = $this->getMock('Magento\App\Dir', array('getDir'), array(), '', false);
        $dirsMock->expects($this->any())
            ->method('getDir')
            ->with(\Magento\App\Dir::MEDIA)
            ->will($this->returnValue(__DIR__ . DIRECTORY_SEPARATOR . '_files'));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $arguments = array(
            'storeConfig' => $storeConfig,
            'urlBuilder' => $urlBuilder,
            'helperFactory' => $helperFactory,
            'dirs' => $dirsMock
        );
        $block = $objectManager->getObject('Magento\Page\Block\Html\Header', $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }
}
