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
 * @group module:Mage_Core
 */
class Mage_Core_Model_Resource_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Cache
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Resource_Cache();
    }

    /**
     * @magentoConfigFixture global/resources/db/table_prefix prefix_
     */
    public function testGetTable()
    {
        $this->assertEquals('prefix_core_cache_option', $this->_model->getTable('core_cache_option'));
        $this->assertEquals('prefix_core_cache_option', $this->_model->getTable(array('core_cache', 'option')));
    }

    public function testUniqueFields()
    {
        $fields = array('field' => 'text');
        $this->_model->addUniqueField($fields);
        $this->assertEquals(array($fields), $this->_model->getUniqueFields());
        $this->_model->resetUniqueField();
        $this->assertEquals(array(), $this->_model->getUniqueFields());
    }

    public function testHasDataChanged()
    {
        $object = new Varien_Object(
            array(
                'code'  => 'value1',
                'value' => 'value2'
            )
        );
        $this->assertTrue($this->_model->hasDataChanged($object));

        $object->setOrigData();
        $this->assertFalse($this->_model->hasDataChanged($object));
        $object->setData('code', 'v1');
        $this->assertTrue($this->_model->hasDataChanged($object));
    }

    public function testGetSaveAllOptions()
    {
        $options = $this->_model->getAllOptions();
        $this->assertEquals(array('config' => 1), $options);
        $options['test_option'] = 1;
        $this->_model->saveAllOptions($options);
        try {
            $this->assertEquals($options, $this->_model->getAllOptions());
        } catch (Exception $e) {
            unset($options['test_option']);
            $this->_model->saveAllOptions($options);
            throw $e;
        }

        unset($options['test_option']);
        $this->_model->saveAllOptions($options);
        $this->assertEquals($options, $this->_model->getAllOptions());
    }
}
