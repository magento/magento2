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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Db_UpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Automatic updates must be enabled/disabled according to config flags
     *
     * @dataProvider updateSchemeAndDataConfigDataProvider
     */
    public function testUpdateSchemeAndDataConfig($configXml, $appMode, $expectedUpdates)
    {
        // Configuration
        $configuration = new Varien_Simplexml_Config($configXml);
        $storage = $this->getMock('Mage_Core_Model_Config_Storage', array(), array(), '', false);
        $storage->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));
        $modulesConfig = new Mage_Core_Model_Config_Modules($storage);

        // Data updates model
        $updateCalls = $expectedUpdates ? 1 : 0;
        $setupModel = $this->getMock('Mage_Core_Model_Resource_Setup', array(), array(), '', false);
        $setupModel->expects($this->exactly($updateCalls))
            ->method('applyUpdates');
        $setupModel->expects($this->exactly($updateCalls))
            ->method('applyDataUpdates');

        $factory = $this->getMock('Mage_Core_Model_Resource_SetupFactory', array(), array(), '', false);
        $factory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($setupModel));

        // Application state
        $appState = $this->getMock('Mage_Core_Model_App_State', array(), array(), '', false);
        $appState->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $appState->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($appMode));
        $updater = new Mage_Core_Model_Db_Updater($modulesConfig, $factory, $appState);

        // Run and verify
        $updater->updateScheme();
        $updater->updateData();
    }

    public static function updateSchemeAndDataConfigDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        return array(
            'updates (default config)' => array(
                file_get_contents($fixturePath . 'config.xml'),
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                true
            ),
            'no updates when skipped' => array(
                file_get_contents($fixturePath . 'config_skip_updates.xml'),
                Mage_Core_Model_App_State::MODE_DEFAULT,
                false
            ),
            'updates when skipped, if in dev mode' => array(
                file_get_contents($fixturePath . 'config_skip_updates.xml'),
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                true
            ),
            'skipped updates, even in dev mode' => array(
                file_get_contents($fixturePath . 'config_skip_updates_even_in_dev_mode.xml'),
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                false
            )
        );
    }
}
