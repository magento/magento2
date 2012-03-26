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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Catalog
 */
class Mage_Catalog_Model_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Abstract
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Mage_Catalog_Model_Abstract');

        $resourceProperty = new ReflectionProperty(get_class($this->_model), '_resourceName');
        $resourceProperty->setAccessible(true);
        $resourceProperty->setValue($this->_model, 'Mage_Catalog_Model_Resource_Product');

        $collectionProperty = new ReflectionProperty(get_class($this->_model), '_resourceCollectionName');
        $collectionProperty->setAccessible(true);
        $collectionProperty->setValue($this->_model, 'Mage_Catalog_Model_Resource_Product_Collection');
    }

    /**
     * @covers Mage_Catalog_Model_Abstract::lockAttribute
     * @covers Mage_Catalog_Model_Abstract::unlockAttribute
     * @covers Mage_Catalog_Model_Abstract::unlockAttributes
     * @covers Mage_Catalog_Model_Abstract::getLockedAttributes
     * @covers Mage_Catalog_Model_Abstract::hasLockedAttributes
     * @covers Mage_Catalog_Model_Abstract::isLockedAttribute
     */
    public function testLockedAttributeApi()
    {
        $this->assertEquals(array(), $this->_model->getLockedAttributes());
        $this->assertFalse($this->_model->hasLockedAttributes());
        $this->assertFalse($this->_model->isLockedAttribute('some_code'));

        $this->_model->lockAttribute('code');
        $this->assertTrue($this->_model->isLockedAttribute('code'));
        $this->assertEquals(array('code'), $this->_model->getLockedAttributes());
        $this->assertTrue($this->_model->hasLockedAttributes());

        $this->_model->unlockAttribute('code');
        $this->assertFalse($this->_model->isLockedAttribute('code'));

        $this->_model->lockAttribute('code1');
        $this->_model->lockAttribute('code2');
        $this->_model->unlockAttributes();
        $this->assertEquals(array(), $this->_model->getLockedAttributes());
        $this->assertFalse($this->_model->hasLockedAttributes());
    }

    public function testSetData()
    {
        // locked filter on setting all
        $this->_model->lockAttribute('key1');
        $this->_model->setData(array('key1' => 'value1', 'key2' => 'value2'));
        $this->assertEquals(array('key2' => 'value2'), $this->_model->getData());

        // locked filter per setting one
        $this->_model->setData('key1', 'value1');
        $this->_model->setData('key3', 'value3');
        $this->assertEquals(array('key2' => 'value2', 'key3' => 'value3'), $this->_model->getData());

        // set one with read only
        $this->_model->unlockAttributes()->unsetData();
        $this->_model->setIsReadonly(true);
        $this->_model->setData(uniqid(), uniqid());
        $this->assertEquals(array(), $this->_model->getData());
    }

    public function testUnsetData()
    {
        $data = array('key1' => 'value1', 'key2' => 'value2');
        $this->_model->setData($data);

        // unset one locked
        $this->_model->lockAttribute('key1')->unsetData('key1');
        $this->assertEquals($data, $this->_model->getData());

        // unset all with read only
        $this->_model->setIsReadonly(true)->unsetData();
        $this->assertEquals($data, $this->_model->getData());

        // unset all
        $this->_model->unlockAttributes()->setIsReadonly(false)->unsetData();
        $this->assertEquals(array(), $this->_model->getData());
    }

    public function testGetResourceCollection()
    {
        $this->_model->setStoreId(99);
        $collection = $this->_model->getResourceCollection();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Collection_Abstract', $collection);
        $this->assertEquals(99, $collection->getStoreId());
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/products.php
     */
    public function testLoadByAttribute()
    {
        $object = $this->_model->loadByAttribute('sku', 'simple');
        $this->assertNotSame($object, $this->_model);
        $this->assertEquals(1, $object->getId()); // fixture

        $result = $this->_model->loadByAttribute('sku', uniqid()); // specifying wrong attribute code leads to fatal
        $this->assertFalse($result);
    }

    public function testGetStore()
    {
        $store = $this->_model->getStore();
        $this->assertSame($store, Mage::app()->getStore());
    }

    public function testGetWebsiteStoreIds()
    {
        $ids = $this->_model->getWebsiteStoreIds();
        $storeId = Mage::app()->getStore()->getId();
        $this->assertEquals(array($storeId => $storeId), $ids);
    }

    public function testSetGetAttributeDefaultValue()
    {
        $this->assertFalse($this->_model->getAttributeDefaultValue('key'));
        $this->_model->setAttributeDefaultValue('key', 'value');
        $this->assertEquals('value', $this->_model->getAttributeDefaultValue('key'));
    }

    public function testSetGetExistsStoreValueFlag()
    {
        $this->assertFalse($this->_model->getExistsStoreValueFlag('key'));
        $this->_model->setExistsStoreValueFlag('key');
        $this->assertTrue($this->_model->getExistsStoreValueFlag('key'));
    }

    /**
     * @covers Mage_Catalog_Model_Abstract::isDeleteable
     * @covers Mage_Catalog_Model_Abstract::setIsDeleteable
     */
    public function testIsDeleteable()
    {
        $this->assertTrue($this->_model->isDeleteable());
        $this->_model->setIsDeleteable(false);
        $this->assertFalse($this->_model->isDeleteable());
    }

    /**
     * @covers Mage_Catalog_Model_Abstract::isReadonly
     * @covers Mage_Catalog_Model_Abstract::setIsReadonly
     */
    public function testIsReadonly()
    {
        $this->assertFalse($this->_model->isReadonly());
        $this->_model->setIsReadonly(true);
        $this->assertTrue($this->_model->isReadonly());
    }
}
