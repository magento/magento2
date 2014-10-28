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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Fieldset
 */
namespace Magento\Framework\Data\Form\Element;

class FieldsetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected $_fieldset;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $elementFactory \Magento\Framework\Data\Form\ElementFactory */
        $elementFactory = $objectManager->create('Magento\Framework\Data\Form\ElementFactory');
        $this->_fieldset = $elementFactory->create('Magento\Framework\Data\Form\Element\Fieldset', array());
    }

    /**
     * @param array $fields
     */
    protected function _fillFieldset(array $fields)
    {
        foreach ($fields as $field) {
            $this->_fieldset->addField($field[0], $field[1], $field[2], $field[3], $field[4]);
        }
    }

    /**
     * Test whether fieldset contains advanced section or not
     *
     * @dataProvider fieldsDataProvider
     */
    public function testHasAdvanced(array $fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertEquals($expect, $this->_fieldset->hasAdvanced());
    }

    /**
     * Test getting advanced section label
     */
    public function testAdvancedLabel()
    {
        $this->assertEmpty($this->_fieldset->getAdvancedLabel());
        $label = 'Test Label';
        $this->_fieldset->setAdvancedLabel($label);
        $this->assertEquals($label, $this->_fieldset->getAdvancedLabel());
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'code',
                        'text',
                        array('name' => 'code', 'label' => 'Name', 'class' => 'required-entry', 'required' => true),
                        false,
                        false
                    ),
                    array(
                        'tax_rate',
                        'multiselect',
                        array(
                            'name' => 'tax_rate',
                            'label' => 'Tax Rate',
                            'class' => 'required-entry',
                            'values' => array('A', 'B', 'C'),
                            'value' => 1,
                            'required' => true
                        ),
                        false,
                        false
                    ),
                    array(
                        'priority',
                        'text',
                        array(
                            'name' => 'priority',
                            'label' => 'Priority',
                            'class' => 'validate-not-negative-number',
                            'value' => 1,
                            'required' => true,
                            'note' => 'Tax rates at the same priority are added, others are compounded.'
                        ),
                        false,
                        true
                    ),
                    array(
                        'priority',
                        'text',
                        array(
                            'name' => 'priority',
                            'label' => 'Priority',
                            'class' => 'validate-not-negative-number',
                            'value' => 1,
                            'required' => true,
                            'note' => 'Tax rates at the same priority are added, others are compounded.'
                        ),
                        false,
                        true
                    )
                ),
                true
            ),
            array(
                array(
                    array(
                        'code',
                        'text',
                        array('name' => 'code', 'label' => 'Name', 'class' => 'required-entry', 'required' => true),
                        false,
                        false
                    ),
                    array(
                        'tax_rate',
                        'multiselect',
                        array(
                            'name' => 'tax_rate',
                            'label' => 'Tax Rate',
                            'class' => 'required-entry',
                            'values' => array('A', 'B', 'C'),
                            'value' => 1,
                            'required' => true
                        ),
                        false,
                        false
                    )
                ),
                false
            )
        );
    }

    /**
     * @dataProvider getChildrenDataProvider
     */
    public function testGetChildren($fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertCount($expect, $this->_fieldset->getChildren());
    }

    /**
     * @return array
     */
    public function getChildrenDataProvider()
    {
        $data = $this->fieldsDataProvider();
        $textField = $data[1][0][0];
        $fieldsetField = $textField;
        $fieldsetField[1] = 'fieldset';
        $result = array(array(array($fieldsetField), 0), array(array($textField), 1));
        return $result;
    }

    /**
     * @dataProvider getBasicChildrenDataProvider
     * @param array $fields
     * @param int $expect
     */
    public function testGetBasicChildren($fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertCount($expect, $this->_fieldset->getBasicChildren());
    }

    /**
     * @dataProvider getBasicChildrenDataProvider
     * @param array $fields
     * @param int $expect
     */
    public function testGetCountBasicChildren($fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertEquals($expect, $this->_fieldset->getCountBasicChildren());
    }

    /**
     * @return array
     */
    public function getBasicChildrenDataProvider()
    {
        $data = $this->getChildrenDataProvider();
        // set isAdvanced flag
        $data[0][0][0][4] = true;
        return $data;
    }

    /**
     * @dataProvider getAdvancedChildrenDataProvider
     * @param array $fields
     * @param int $expect
     */
    public function testGetAdvancedChildren($fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertCount($expect, $this->_fieldset->getAdvancedChildren());
    }

    /**
     * @return array
     */
    public function getAdvancedChildrenDataProvider()
    {
        $data = $this->getChildrenDataProvider();
        // change isAdvanced flag
        $data[0][0][0][4] = true;
        // change expected results
        $data[0][1] = 1;
        $data[1][1] = 0;
        return $data;
    }

    /**
     * @dataProvider getSubFieldsetDataProvider
     * @param array $fields
     * @param int $expect
     */
    public function testGetSubFieldset($fields, $expect)
    {
        $this->_fillFieldset($fields);
        $this->assertCount($expect, $this->_fieldset->getAdvancedChildren());
    }

    /**
     * @return array
     */
    public function getSubFieldsetDataProvider()
    {
        $data = $this->fieldsDataProvider();
        $textField = $data[1][0][0];
        $fieldsetField = $textField;
        $fieldsetField[1] = 'fieldset';
        $advancedFieldsetFld = $fieldsetField;
        // set isAdvenced flag
        $advancedFieldsetFld[4] = true;
        $result = array(array(array($fieldsetField, $textField, $advancedFieldsetFld), 1));
        return $result;
    }
}
