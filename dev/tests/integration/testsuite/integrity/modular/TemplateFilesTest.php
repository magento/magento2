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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Modular_TemplateFilesTest extends Magento_Test_TestCase_IntegrityAbstract
{
    /**
     * @param string $module
     * @param string $template
     * @param string $class
     * @param string $area
     * @dataProvider allTemplatesDataProvider
     */
    public function testAllTemplates($module, $template, $class, $area)
    {
        if ((strpos($class, 'Enterprise') === 0 && strpos($class, '_Adminhtml') === false) || $area == 'frontend') {
            $package = 'enterprise';
        } else {
            $package = 'default';
        }

        $params = array(
            '_area'     => $area,
            '_package'  => $package,
            '_theme'    => 'default',
            '_module'   => $module
        );
        $file = Mage::getDesign()->getTemplateFilename($template, $params);
        $this->assertFileExists($file, "Block class: {$class}");
    }

    /**
     * @return array
     */
    public function allTemplatesDataProvider()
    {
        $excludeList = array(
            'Mage_Checkout_Block_Onepage_Review',
            'Enterprise_GiftRegistry_Block_Items',
            'Enterprise_Search_Block_Catalog_Layer_View',
            'Enterprise_Search_Block_Catalogsearch_Layer',
        );

        $templates = array();
        foreach ($this->_getEnabledModules() as $module) {
            $blocks = $this->_getModuleBlocks($module);
            foreach ($blocks as $blockClass) {
                $isClassValid = strpos($blockClass, 'Abstract') === false
                    && strpos($blockClass, 'Interface') === false
                    && !in_array($blockClass, $excludeList);
                if ($isClassValid) {
                    $class = new ReflectionClass($blockClass);
                    if ($class->isAbstract()) {
                        continue;
                    }
                    $block = new $blockClass;
                    if ($block instanceof Mage_Core_Block_Template) {
                        $template = $block->getTemplate();
                        if ($template && !$this->_isFileForDisabledModule($template)) {
                            $area = $module == 'Mage_Install' ? 'install' : 'frontend';
                            $useAdminArea = $module == 'Mage_Adminhtml' || strpos($blockClass, '_Adminhtml_')
                                || ($block instanceof Mage_Adminhtml_Block_Template);
                            if ($useAdminArea) {
                                $area = 'adminhtml';
                            }
                            $templates[] = array($module, $template, $blockClass, $area);
                        }
                    }
                }
            }
        }
        return $templates;
    }

    /**
     * Get all block classes of specified module
     *
     * @param  string $module
     * @return array
     */
    protected function _getModuleBlocks($module)
    {
        $classes = array();
        $dir = Mage::getConfig()->getModuleDir('', $module) . DIRECTORY_SEPARATOR . 'Block';
        if (!is_dir($dir)) {
            return $classes;
        }
        $directory  = new RecursiveDirectoryIterator($dir);
        $iterator   = new RecursiveIteratorIterator($directory);
        $regex      = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $class = str_replace($dir. DIRECTORY_SEPARATOR, '', $file[0]);
            $class = str_replace('.php', '', $class);
            $class = str_replace(DIRECTORY_SEPARATOR, '_', $class);
            $class = $module . '_Block_' . $class;
            $classes[] = $class;
        }
        return $classes;
    }

    /**
     * @param string $blockClass
     * @dataProvider blocksWithGlobalTemplatesDataProvider
     */
    public function testBlocksWithGlobalTemplates($blockClass)
    {
        $block = new $blockClass;
        list($module) = explode('_Block_', $blockClass);
        $file = Mage::getDesign()->getTemplateFilename($block->getTemplate(), array(
            '_area' => 'adminhtml',
            '_package'  => 'default',
            '_theme'    => 'default',
            '_module' => $module,
        ));
        $this->assertFileExists($file, $blockClass);
    }

    /**
     * @return array
     */
    public function blocksWithGlobalTemplatesDataProvider()
    {
        // All possible files to test
        $allBlocks = array(
            array('Mage_Payment_Block_Form_Cc'),
            array('Mage_Payment_Block_Form_Ccsave'),
            array('Mage_Payment_Block_Form_Checkmo'),
            array('Mage_Payment_Block_Form_Purchaseorder'),
            array('Mage_Payment_Block_Info_Cc'),
            array('Mage_Payment_Block_Info_Ccsave'),
            array('Mage_Payment_Block_Info_Checkmo'),
            array('Mage_Payment_Block_Info_Purchaseorder'),
            array('Mage_Payment_Block_Info'),
            array('Mage_Sales_Block_Payment_Form_Billing_Agreement'),
            array('Mage_Sales_Block_Payment_Info_Billing_Agreement'),
            array('Mage_Paygate_Block_Authorizenet_Form_Cc'),
            array('Mage_Paygate_Block_Authorizenet_Info_Cc'),
            array('Mage_Paypal_Block_Payment_Info'),
            array('Mage_Authorizenet_Block_Directpost_Form'),
            array('Mage_Authorizenet_Block_Directpost_Iframe'),
            array('Mage_Ogone_Block_Info'),
            array('Phoenix_Moneybookers_Block_Info'),
        );

        return $this->_removeDisabledModulesFiles($allBlocks);
    }

    /**
     * Scans array of block class names and removes the ones that belong to disabled modules.
     * Thus we won't test them.
     *
     * @param array $allBlocks
     * @return array
     */
    protected function _removeDisabledModulesFiles($allBlocks)
    {
        $enabledModules = $this->_getEnabledModules();
        $result = array();
        foreach ($allBlocks as $blockInfo) {
            $block = $blockInfo[0];
            if (preg_match('/^(.*?)_Block/', $block, $matches)) {
                $module = $matches[1];
                if (!isset($enabledModules[$module])) {
                    continue;
                }
            }
            $result[] = $blockInfo;
        }
        return $result;
    }
}
