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
        $objectManager = Mage::getObjectManager();

        // disable config caching to not pollute it
        /** @var $cacheTypes Mage_Core_Model_Cache_Types */
        $cacheTypes = $objectManager->get('Mage_Core_Model_Cache_Types');
        $cacheTypes->setEnabled(Mage_Core_Model_Cache_Type_Config::TYPE_IDENTIFIER, false);

        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = $objectManager->get('Mage_Core_Model_Dir');
        $modulesDir = $dirs->getDir(Mage_Core_Model_Dir::MODULES);

        $fileList = glob($modulesDir . '/*/*/etc/adminhtml/system.xml');

        $configMock = $this->getMock(
            'Mage_Core_Model_Config_Modules_Reader', array('getModuleConfigurationFiles', 'getModuleDir'),
            array(), '', false
        );
        $configMock->expects($this->any())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue($fileList))
        ;
        $configMock->expects($this->any())
            ->method('getModuleDir')
            ->with('etc', 'Mage_Backend')
            ->will($this->returnValue($modulesDir . '/Mage/Backend/etc'))
        ;
        try {
            $objectManager->create('Mage_Backend_Model_Config_Structure_Reader', array(
                'moduleReader' => $configMock,
                'runtimeValidation' => true,
            ));
        } catch (Magento_Exception $exp) {
            $this->fail($exp->getMessage());
        }
    }
}
