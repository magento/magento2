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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Integrity_Modular_SystemConfigFilesTest extends PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $fileList = glob(Mage::getBaseDir('app') . '/*/*/*/*/etc/adminhtml/system.xml');
        try {
            $configMock = $this->getMock(
                'Mage_Core_Model_Config_Modules_Reader', array('getModuleConfigurationFiles', 'getModuleDir'),
                array(), '', false
            );
            $configMock->expects($this->any())
                ->method('getModuleConfigurationFiles')
                ->will($this->returnValue($fileList));
            $configMock->expects($this->any())
                ->method('getModuleDir')
                ->will($this->returnValue(Mage::getBaseDir('app') . '/code/core/Mage/Backend/etc'));
            $cacheMock = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);
            $cacheMock->expects($this->any())->method('canUse')->will($this->returnValue(false));
            $converter = new Mage_Backend_Model_Config_Structure_Converter(
                new Mage_Backend_Model_Config_Structure_Mapper_Factory(Mage::getObjectManager())
            );
            new Mage_Backend_Model_Config_Structure_Reader(
                $cacheMock, $configMock, $converter, true
            );
        } catch (Magento_Exception $exp) {
            $this->fail($exp->getMessage());
        }
    }
}
