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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_System_Config_FormTest extends PHPUnit_Framework_TestCase
{
    public function testDependenceHtml()
    {
        $layout = new Mage_Core_Model_Layout();
        $block = $layout->createBlock('Mage_Adminhtml_Block_System_Config_Form', 'block');
        $block->setArea('adminhtml');

        $childBlock = $layout->addBlock('Mage_Core_Block_Text', 'element_dependence', 'block');

        $expectedValue = 'dependence_html_relations';
        $this->assertNotContains($expectedValue, $block->toHtml());

        $childBlock->setText($expectedValue);
        $this->assertContains($expectedValue, $block->toHtml());
    }

    /**
     * @covers Mage_Adminhtml_Block_System_Config_Form::initFields
     * @param $section Mage_Core_Model_Config_Element
     * @param $group Mage_Core_Model_Config_Element
     * @param $field Mage_Core_Model_Config_Element
     * @param array $configData
     * @param bool $expectedUseDefault
     * @dataProvider initFieldsInheritCheckboxDataProvider
     */
    public function testInitFieldsUseDefaultCheckbox($section, $group, $field, array $configData, $expectedUseDefault)
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset($section->getName() . '_' . $group->getName(), array());

        $block = new Mage_Adminhtml_Block_System_Config_FormStub();
        $block->setScope(Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES);
        $block->setStubConfigData($configData);
        $block->initFields($fieldset, $group, $section);

        $fieldsetSel = 'fieldset';
        $valueSel = sprintf('input#%s_%s_%s', $section->getName(), $group->getName(), $field->getName());
        $valueDisabledSel = sprintf('%s[disabled="disabled"]', $valueSel);
        $useDefaultSel = sprintf('input#%s_%s_%s_inherit.checkbox', $section->getName(), $group->getName(),
            $field->getName());
        $useDefaultCheckedSel = sprintf('%s[checked="checked"]', $useDefaultSel);
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertSelectCount($fieldsetSel, true, $fieldsetHtml, 'Fieldset HTML is invalid');
        $this->assertSelectCount($valueSel, true, $fieldsetHtml, 'Field input not found in fieldset HTML');
        $this->assertSelectCount($useDefaultSel, true, $fieldsetHtml,
            '"Use Default" checkbox not found in fieldset HTML');

        if ($expectedUseDefault) {
            $this->assertSelectCount($useDefaultCheckedSel, true, $fieldsetHtml,
                '"Use Default" checkbox should be checked');
            $this->assertSelectCount($valueDisabledSel, true, $fieldsetHtml,
                'Field input should be disabled');
        } else {
            $this->assertSelectCount($useDefaultCheckedSel, false, $fieldsetHtml,
                '"Use Default" checkbox should not be checked');
            $this->assertSelectCount($valueDisabledSel, false, $fieldsetHtml,
                'Field input should not be disabled');
        }
    }

    /**
     * @return array
     */
    public function initFieldsInheritCheckboxDataProvider()
    {
        $section = new Mage_Core_Model_Config_Element(file_get_contents(__DIR__ . '/_files/test_section_config.xml'));
        // @codingStandardsIgnoreStart
        $group = $section->groups->test_group;
        $field = $group->fields->test_field;
        $fieldPath = (string) $field->config_path;
        // @codingStandardsIgnoreEnd

        return array(
            array($section, $group, $field, array(), true),
            array($section, $group, $field, array($fieldPath => null), false),
            array($section, $group, $field, array($fieldPath => ''), false),
            array($section, $group, $field, array($fieldPath => 'value'), false),
        );
    }
}
