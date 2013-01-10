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
 * @category    Varien
 * @package     Varien_Data
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for Varien_Data_Form_Element_Fieldset
 */
class Varien_Data_Form_Element_FieldsetTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_fieldset = new Varien_Data_Form_Element_Fieldset(array());
    }

    /**
     * Test whether fieldset contains advanced section or not
     *
     * @dataProvider fieldsDataProvider
     */
    public function testHasAdvanced(array $fields, $expect)
    {
        foreach ($fields as $field) {
            $this->_fieldset->addField(
                $field[0],
                $field[1],
                $field[2],
                $field[3],
                $field[4]
            );
        }

        $this->assertEquals(
            $expect,
            $this->_fieldset->hasAdvanced()
        );
    }

    /**
     * Test getting advanced section label
     */
    public function testAdvancedLabel()
    {
        $this->assertNotEmpty($this->_fieldset->getAdvancedLabel());
        $label = 'Test Label';
        $this->_fieldset->setAdvancedLabel($label);
        $this->assertEquals($label, $this->_fieldset->getAdvancedLabel());
    }

    /**
     * Data provider to fill fieldset with elements
     */
    public function fieldsDataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'code',
                        'text',
                        array(
                            'name'     => 'code',
                            'label'    => 'Name',
                            'class'    => 'required-entry',
                            'required' => true,
                        ),
                        false,
                        false
                    ),
                    array(
                        'tax_rate',
                        'multiselect',
                        array(
                            'name'     => 'tax_rate',
                            'label'    => 'Tax Rate',
                            'class'    => 'required-entry',
                            'values'   => array('A', 'B', 'C'),
                            'value'    => 1,
                            'required' => true,
                        ),
                        false,
                        false
                    ),
                    array(
                        'priority',
                        'text',
                        array(
                            'name'     => 'priority',
                            'label'    => 'Priority',
                            'class'    => 'validate-not-negative-number',
                            'value'    => 1,
                            'required' => true,
                            'note'     => 'Tax rates at the same priority are added, others are compounded.',
                        ),
                        false,
                        true
                    ),
                    array(
                        'priority',
                        'text',
                        array(
                            'name'     => 'priority',
                            'label'    => 'Priority',
                            'class'    => 'validate-not-negative-number',
                            'value'    => 1,
                            'required' => true,
                            'note'     => 'Tax rates at the same priority are added, others are compounded.',
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
                        array(
                            'name'     => 'code',
                            'label'    => 'Name',
                            'class'    => 'required-entry',
                            'required' => true,
                        ),
                        false,
                        false
                    ),
                    array(
                        'tax_rate',
                        'multiselect',
                        array(
                            'name'     => 'tax_rate',
                            'label'    => 'Tax Rate',
                            'class'    => 'required-entry',
                            'values'   => array('A', 'B', 'C'),
                            'value'    => 1,
                            'required' => true,
                        ),
                        false,
                        false
                    )
                ),
                false
            )
        );
    }
}