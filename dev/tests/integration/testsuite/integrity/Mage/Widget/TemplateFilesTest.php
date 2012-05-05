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
 * @package     Mage_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Mage_Widget_TemplateFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if all the declared widget templates actually exist
     *
     * @param string $class
     * @param string $template
     * @dataProvider widgetTemplatesDataProvider
     */
    public function testWidgetTemplates($class, $template)
    {
        $block = new $class;
        /** @var Mage_Core_Block_Template $block */
        $this->assertInstanceOf('Mage_Core_Block_Template', $block);
        $block->setTemplate((string)$template);
        $this->assertFileExists($block->getTemplateFile());
    }

    /**
     * Collect all declared widget blocks and templates
     *
     * @return array
     */
    public function widgetTemplatesDataProvider()
    {
        $result = array();
        $model = new Mage_Widget_Model_Widget;
        foreach ($model->getWidgetsArray() as $row) {
            $instance = new Mage_Widget_Model_Widget_Instance;
            $config = $instance->setType($row['type'])->getWidgetConfig();
            $class = Mage::getConfig()->getBlockClassName($row['type']);
            if (is_subclass_of($class, 'Mage_Core_Block_Template')) {
                $templates = $config->xpath('/widgets/' . $row['code'] . '/parameters/template/values/*/value');
                foreach ($templates as $template) {
                    $result[] = array($class, (string)$template);
                }
            }
        }
        return $result;
    }
}
