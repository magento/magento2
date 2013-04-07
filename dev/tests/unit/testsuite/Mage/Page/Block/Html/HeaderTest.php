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
 * @category    Mage
 * @package     Mage_Page
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Page_Block_Html_HeaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_Page_Block_Html_Header::getLogoSrc
     */
    public function testGetLogoSrc()
    {
        $storeConfig = $this->getMock('Mage_Core_Model_Store_Config', array('getConfig'));
        $storeConfig->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue('default/image.gif'));

        $urlBuilder = $this->getMock('Mage_Core_Model_Url', array('getBaseUrl'));
        $urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://localhost/pub/media/'));

        $helper = $this->getMock('Mage_Core_Helper_File_Storage_Database',
            array('checkDbUsage'), array(), '', false, false
        );
        $helper->expects($this->once())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));

        $helperFactory = $this->getMock('Mage_Core_Model_Factory_Helper', array('get'));
        $helperFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($helper));

        $dirsMock = $this->getMock('Mage_Core_Model_Dir', array('getDir'), array(), '', false);
        $dirsMock->expects($this->any())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::MEDIA)
            ->will($this->returnValue(__DIR__ . DIRECTORY_SEPARATOR . '_files'));

        $objectManager = new Magento_Test_Helper_ObjectManager($this);

        $arguments = array(
            'storeConfig' => $storeConfig,
            'urlBuilder' => $urlBuilder,
            'helperFactory' => $helperFactory,
            'dirs' => $dirsMock
        );
        $block = $objectManager->getObject('Mage_Page_Block_Html_Header', $arguments);

        $this->assertEquals('http://localhost/pub/media/logo/default/image.gif', $block->getLogoSrc());
    }
}
