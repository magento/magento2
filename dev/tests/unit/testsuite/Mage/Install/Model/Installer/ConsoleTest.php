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
 * @package     Mage_Install
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Model_Installer_ConsoleTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateEncryptionKey()
    {
        /** @var $model Mage_Install_Model_Installer_Console */
        $model = $this->getMock('Mage_Install_Model_Installer_Console', null, array(), '', false);
        /** @var $helper Mage_Core_Helper_Data */
        $helper = $this->getMock('Mage_Core_Helper_Data', array('getRandomString'), array(), '', false);
        $helper->expects($this->exactly(2))->method('getRandomString')->with(10)
            ->will($this->onConsecutiveCalls('1234567890', '0123456789'));
        $this->assertNotEquals($model->generateEncryptionKey($helper), $model->generateEncryptionKey($helper));
    }
}
