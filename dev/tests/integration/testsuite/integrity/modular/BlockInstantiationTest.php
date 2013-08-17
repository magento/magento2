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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * This test ensures that all blocks have the appropriate constructor arguments that allow
 * them to be instantiated via the objectManager.
 *
 * @magentoAppIsolation
 */
class Integrity_Modular_BlockInstantiationTest extends Magento_Test_TestCase_IntegrityAbstract
{
    /**
     * @param string $module
     * @param string $class
     * @param string $area
     * @dataProvider allBlocksDataProvider
     */
    public function testBlockInstantiation($module, $class, $area)
    {
        $this->assertNotEmpty($module);
        $this->assertTrue(class_exists($class), "Block class: {$class}");
        Mage::getConfig()->setCurrentAreaCode($area);
        $block = Mage::getModel($class);
        $this->assertNotNull($block);
    }

    /**
     * @return array
     */
    public function allBlocksDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website Mage_Core_Model_Website */
            Mage::app()->getStore()->setWebsiteId(0);

            $enabledModules = $this->_getEnabledModules();
            $skipBlocks = $this->_getBlocksToSkip();
            $templateBlocks = array();
            $blockMods = Utility_Classes::collectModuleClasses('Block');
            foreach ($blockMods as $blockClass => $module) {
                if (!isset($enabledModules[$module]) || isset($skipBlocks[$blockClass])) {
                    continue;
                }
                $class = new ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf('Mage_Core_Block_Template')) {
                    continue;
                }
                $templateBlocks = $this->_addBlock($module, $blockClass, $class, $templateBlocks);
            }
            return $templateBlocks;
        } catch (Exception $e) {
            trigger_error("Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'."
                . " Exception: {$e}", E_USER_ERROR);
        }
    }

    /**
     * Loads block classes, that should not be instantiated during the instantiation test
     *
     * @return array
     */
    protected function _getBlocksToSkip()
    {
        $result = array();
        foreach (glob(__DIR__ . '/_files/skip_blocks*.php') as $file) {
            $blocks = include $file;
            $result = array_merge($result, $blocks);
        }
        return array_combine($result, $result);
    }

    /**
     * @param $module
     * @param $blockClass
     * @param $class
     * @param $templateBlocks
     * @return mixed
     */
    private function _addBlock($module, $blockClass, $class, $templateBlocks)
    {
        $area = 'frontend';
        if ($module == 'Mage_Install') {
            $area = 'install';
        } elseif ($module == 'Mage_Adminhtml' || strpos($blockClass, '_Adminhtml_')
            || strpos($blockClass, '_Backend_')
            || $class->isSubclassOf('Mage_Backend_Block_Template')
        ) {
            $area = 'adminhtml';
        }
        Mage::app()->loadAreaPart(
            Mage_Core_Model_App_Area::AREA_ADMINHTML,
            Mage_Core_Model_App_Area::PART_CONFIG
        );
        $templateBlocks[$module . ', ' . $blockClass . ', ' . $area]
            = array($module, $blockClass, $area);
        return $templateBlocks;
    }
}
