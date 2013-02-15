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
 * @magentoAppIsolation
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
        Mage::getDesign()->setDefaultDesignTheme();
        // intentionally to make sure the module files will be requested
        $params = array(
            'area'       => $area,
            'themeModel' => Mage::getModel('Mage_Core_Model_Theme'),
            'module'     => $module
        );
        $file = Mage::getDesign()->getFilename($template, $params);
        $this->assertFileExists($file, "Block class: {$class}");
    }

    /**
     * @return array
     */
    public function allTemplatesDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website Mage_Core_Model_Website */
            Mage::app()->getStore()->setWebsiteId(0);

            $templates = array();
            foreach (Utility_Classes::collectModuleClasses('Block') as $blockClass => $module) {
                if (!in_array($module, $this->_getEnabledModules())) {
                    continue;
                }
                $class = new ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf('Mage_Core_Block_Template')) {
                    continue;
                }

                $area = 'frontend';
                if ($module == 'Mage_Install') {
                    $area = 'install';
                } elseif ($module == 'Mage_Adminhtml' || strpos($blockClass, '_Adminhtml_')
                    || strpos($blockClass, '_Backend_')
                    || ($this->_isClassInstanceOf($blockClass, 'Mage_Backend_Block_Template'))
                ) {
                    $area = 'adminhtml';
                }

            Mage::app()->loadAreaPart(
                Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::AREA_ADMINHTML
            );
            Mage::getConfig()->setCurrentAreaCode($area);

                $block = Mage::getModel($blockClass);
                $template = $block->getTemplate();
                if ($template) {
                    $templates[$module . ', ' . $template . ', ' . $blockClass . ', ' . $area] =
                        array($module, $template, $blockClass, $area);
                }
            }
            return $templates;
        } catch (Exception $e) {
            trigger_error("Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'."
                . " Exception: {$e}", E_USER_ERROR);
        }
    }

    /**
     * @param string $blockClass
     * @param string $parentClass
     * @return bool
     */
    protected function _isClassInstanceOf($blockClass, $parentClass)
    {
        $currentClass = new ReflectionClass($blockClass);
        $supertypes = array();
        do {
            $supertypes = array_merge($supertypes, $currentClass->getInterfaceNames());
            if (!($currentParent = $currentClass->getParentClass())) {
                break;
            }
            $supertypes[] = $currentParent->getName();
            $currentClass = $currentParent;
        } while (true);

        return in_array($parentClass, $supertypes);
    }
}
