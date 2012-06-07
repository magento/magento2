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
        $params = array(
            '_area'    => $area,
            '_package' => false, // intentionally to make sure the module files will be requested
            '_theme'   => false,
            '_module'  => $module
        );
        $file = Mage::getDesign()->getFilename($template, $params);
        $this->assertFileExists($file, "Block class: {$class}");
    }

    /**
     * @return array
     */
    public function allTemplatesDataProvider()
    {
        $templates = array();
        foreach (Utility_Classes::collectModuleClasses('Block') as $blockClass => $module) {
            if (!in_array($module, $this->_getEnabledModules())) {
                continue;
            }
            $class = new ReflectionClass($blockClass);
            if ($class->isAbstract() || !$class->isSubclassOf('Mage_Core_Block_Template')) {
                continue;
            }
            $block = new $blockClass;
            $template = $block->getTemplate();
            if ($template) {
                $area = 'frontend';
                if ($module == 'Mage_Install') {
                    $area = 'install';
                } elseif ($module == 'Mage_Adminhtml' || strpos($blockClass, '_Adminhtml_')
                    || strpos($blockClass, '_Backend_') || ($block instanceof Mage_Backend_Block_Template)
                ) {
                    $area = 'adminhtml';
                }
                $templates[] = array($module, $template, $blockClass, $area);
            }
        }
        return $templates;
    }
}
