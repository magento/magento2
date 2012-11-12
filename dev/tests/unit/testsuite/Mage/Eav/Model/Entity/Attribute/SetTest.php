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
 * @package     Mage_Eav
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Eav_Model_Entity_Attribute_Set
 */
class Mage_Eav_Model_Entity_Attribute_SetTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * @var Mage_Eav_Model_Entity_Attribute_Set
     */
    protected $_model;

    protected function setUp()
    {
        $resource = $this->getMock('Mage_Eav_Model_Resource_Entity_Attribute_Set', array(), array(), '', false);

        $helper = $this->getMock('Mage_Eav_Helper_Data', array('__'));
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));

        $arguments = array(
            'resource'  => $resource,
            'data'      => array('helper' => $helper)
        );
        $this->_model = $this->getModel('Mage_Eav_Model_Entity_Attribute_Set', $arguments);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }


    /**
     * @param string $attributeSetName
     * @param string $exceptionMessage
     * @dataProvider invalidAttributeSetDataProvider
     */
    public function testValidateWithExistingName($attributeSetName, $exceptionMessage)
    {
        $this->_model->getResource()
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(false));

        $this->setExpectedException('Mage_Eav_Exception', $exceptionMessage);
        $this->_model->setAttributeSetName($attributeSetName);
        $this->_model->validate();
    }

    public function testValidateWithNonexistentValidName()
    {
        $this->_model->getResource()
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->_model->setAttributeSetName('nonexistent_name');
        $this->assertTrue($this->_model->validate());
    }

    /**
     * Retrieve data for invalid
     *
     * @return array
     */
    public function invalidAttributeSetDataProvider()
    {
        return array(
            array('', 'Attribute set name is empty.'),
            array('existing_name', 'Attribute set with the "%s" name already exists.')
        );
    }
}
