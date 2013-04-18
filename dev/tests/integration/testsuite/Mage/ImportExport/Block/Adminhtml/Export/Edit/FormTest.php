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
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for block Mage_ImportExport_Block_Adminhtml_Export_Edit_Form
 */
class Mage_ImportExport_Block_Adminhtml_Export_Edit_FormTest extends Mage_Backend_Area_TestCase
{
    /**
     * Testing model
     *
     * @var Mage_ImportExport_Block_Adminhtml_Export_Edit_Form
     */
    protected $_model;

    /**
     * Expected form fieldsets and fields
     * array (
     *     <fieldset_id> => array(
     *         <element_id> => <element_name>,
     *         ...
     *     ),
     *     ...
     * )
     *
     * @var array
     */
    protected $_expectedFields = array(
        'base_fieldset' => array(
            'entity'      => 'entity',
            'file_format' => 'file_format',
        ),
    );

    public function setUp()
    {
        parent::setUp();
        $this->_model = Mage::app()->getLayout()->createBlock('Mage_ImportExport_Block_Adminhtml_Export_Edit_Form');
    }

    /**
     * Test preparing of form
     *
     * @covers Mage_ImportExport_Block_Adminhtml_Export_Edit_Form::_prepareForm
     */
    public function testPrepareForm()
    {
        // invoking _prepareForm
        $this->_model->toHtml();

        // get fieldset list
        $actualFieldsets = array();
        $formElements = $this->_model->getForm()
            ->getElements();
        foreach ($formElements as $formElement) {
            if ($formElement instanceof Varien_Data_Form_Element_Fieldset) {
                $actualFieldsets[] = $formElement;
            }
        }

        // assert fieldsets and fields
        $this->assertSameSize($this->_expectedFields, $actualFieldsets);
        /** @var $actualFieldset Varien_Data_Form_Element_Fieldset */
        foreach ($actualFieldsets as $actualFieldset) {
            $this->assertArrayHasKey($actualFieldset->getId(), $this->_expectedFields);
            $expectedFields = $this->_expectedFields[$actualFieldset->getId()];
            /** @var $actualField Varien_Data_Form_Element_Abstract */
            foreach ($actualFieldset->getElements() as $actualField) {
                $this->assertArrayHasKey($actualField->getId(), $expectedFields);
                $this->assertEquals($expectedFields[$actualField->getId()], $actualField->getName());
            }
        }
    }
}
