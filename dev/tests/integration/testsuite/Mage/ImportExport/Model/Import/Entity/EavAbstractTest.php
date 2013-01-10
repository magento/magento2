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
 * Test class for Mage_ImportExport_Model_Import_Entity_EavAbstract
 */
class Mage_ImportExport_Model_Import_Entity_EavAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var Mage_ImportExport_Model_Import_Entity_EavAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Create all necessary data for tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_EavAbstract', array(),
            '', false);
    }

    /**
     * Unset created data during test
     */
    protected function tearDown()
    {
        unset($this->_model);
        parent::tearDown();
    }

    /**
     * Test for method getAttributeOptions()
     */
    public function testGetAttributeOptions()
    {
        $indexAttributeCode = 'gender';

        /** @var $attributeCollection Mage_Customer_Model_Resource_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $attributeCollection->addFieldToFilter(
            'attribute_code',
            array(
                'in' => array($indexAttributeCode, 'group_id')
            )
        );
        /** @var $attribute Mage_Customer_Model_Attribute */
        foreach ($attributeCollection as $attribute) {
            $index = ($attribute->getAttributeCode() == $indexAttributeCode) ? 'value' : 'label';
            $expectedOptions = array();
            foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                $expectedOptions[strtolower($option[$index])] = $option['value'];
            }
            $actualOptions = $this->_model->getAttributeOptions($attribute, array($indexAttributeCode));
            sort($expectedOptions);
            sort($actualOptions);
            $this->assertEquals($expectedOptions, $actualOptions);
        }
    }
}
