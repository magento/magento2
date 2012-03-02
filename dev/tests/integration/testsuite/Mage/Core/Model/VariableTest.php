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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_VariableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Variable
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Variable();
    }

    public function tearDown()
    {
        $this->_model = null;
    }

    public function testGetSetStoreId()
    {
        $this->_model->setStoreId(1);
        $this->assertEquals(1, $this->_model->getStoreId());
    }

    public function testLoadByCode()
    {
        $this->_model->setData(array(
            'code'  => 'test_code',
            'name'  => 'test_name'
        ));
        $this->_model->save();

        $variable = new Mage_Core_Model_Variable();
        $variable->loadByCode('test_code');
        $this->assertEquals($this->_model->getName(), $variable->getName());
        $this->_model->delete();
    }

    public function testGetValue()
    {
        $html = '<p>test</p>';
        $text = 'test';
        $this->_model->setData(array(
            'code'          => 'test_code',
            'html_value'    => $html,
            'plain_value'   => $text
        ));
        $this->assertEquals($html, $this->_model->getValue());
        $this->assertEquals($html, $this->_model->getValue(Mage_Core_Model_Variable::TYPE_HTML));
        $this->assertEquals($text, $this->_model->getValue(Mage_Core_Model_Variable::TYPE_TEXT));
    }

    public function testValidate()
    {
        $this->assertNotEmpty($this->_model->validate());
        $this->_model->setName('test')
            ->setCode('test');
        $this->assertNotEmpty($this->_model->validate());
        $this->_model->save();
        try {
            $this->assertTrue($this->_model->validate());
            $this->_model->delete();
        } catch (Exception $e) {
            $this->_model->delete();
            throw $e;
        }
    }

    public function testGetVariablesOptionArray()
    {
        $this->assertEquals(array(), $this->_model->getVariablesOptionArray());
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection();
        $collection->setStoreId(1);
        $this->assertEquals(1, $collection->getStoreId(), 'Store id setter and getter');

        $collection->addValuesToResult();
        $this->assertContains('core_variable_value', (string) $collection->getSelect());
    }
}
