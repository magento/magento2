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
 * Varien_Object test case.
 */
class Varien_ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Varien_Object
     */
    private $_object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_object = new Varien_Object();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    /**
     * Tests Varien_Object->__construct()
     */
    public function test__construct()
    {
        $object = new Varien_Object();
        $this->assertEquals(array(), $object->getData());

        $data = array('test' => 'test');
        $object = new Varien_Object($data);
        $this->assertEquals($data, $object->getData());
    }

    /**
     * Tests Varien_Object->isDeleted()
     */
    public function testIsDeleted()
    {
        $this->assertFalse($this->_object->isDeleted());
        $this->_object->isDeleted();
        $this->assertFalse($this->_object->isDeleted());
        $this->_object->isDeleted(true);
        $this->assertTrue($this->_object->isDeleted());
    }

    /**
     * Tests Varien_Object->hasDataChanges()
     */
    public function testHasDataChanges()
    {
        $this->assertFalse($this->_object->hasDataChanges());
        $this->_object->setData('key', 'value');
        $this->assertTrue($this->_object->hasDataChanges(), 'Data changed');

        $object = new Varien_Object(array('key' => 'value'));
        $object->setData('key', 'value');
        $this->assertFalse($object->hasDataChanges(), 'Data not changed');

        $object->setData(array('key' => 'value'));
        $this->assertFalse($object->hasDataChanges(), 'Data not changed (array)');

        $object = new Varien_Object();
        $object->unsetData();
        $this->assertFalse($object->hasDataChanges(), 'Unset data');

        $object = new Varien_Object(array('key'=>null));
        $object->setData('key', null);
        $this->assertFalse($object->hasDataChanges(), 'Null data');
    }

    /**
     * Tests Varien_Object->getId()
     */
    public function testSetGetId()
    {
        $this->_object->setId('test');
        $this->assertEquals('test', $this->_object->getId());
    }

    /**
     * Tests Varien_Object->addData()
     */
    public function testAddData()
    {
        $this->_object->addData(array('test' => 'value'));
        $this->assertEquals('value', $this->_object->getData('test'));

        $this->_object->addData(array('test' => 'value1'));
        $this->assertEquals('value1', $this->_object->getData('test'));

        $this->_object->addData(array('test2' => 'value2'));
        $this->assertEquals(array('test' => 'value1', 'test2' => 'value2'), $this->_object->getData());
    }

    /**
     * Tests Varien_Object->setData()
     */
    public function testSetData()
    {
        $data = array(
            'key1'  => 'value1',
            'key2'  => 'value2',
            'key3'  => 3
        );
        $this->_object->setData($data);
        $this->assertEquals($data, $this->_object->getData());

        $data['key1'] = 1;
        $this->_object->setData('key1', 1);
        $this->assertEquals($data, $this->_object->getData());

        $this->_object->setData('key1');
        $data['key1'] = null;
        $this->assertEquals($data, $this->_object->getData());
    }

    /**
     * Tests Varien_Object->unsetData()
     */
    public function testUnsetData()
    {
        $data = array(
            'key1'  => 'value1',
            'key2'  => 'value2',
            'key3'  => 3,
            'key4'  => 4
        );
        $this->_object->setData($data);

        $this->_object->unsetData('key1');
        unset($data['key1']);
        $this->assertEquals($data, $this->_object->getData());

        $this->_object->unsetData(array('key2', 'key3'));
        unset($data['key2']);
        unset($data['key3']);
        $this->assertEquals($data, $this->_object->getData());

        $this->_object->unsetData();
        $this->assertEquals(array(), $this->_object->getData());
    }

    /**
     * Tests Varien_Object->getData()
     */
    public function testGetData()
    {
        $data = array(
            'key1' => 'value1',
            'key2' => array(
                'subkey2.1' => 'value2.1',
                'subkey2.2' => 'multiline
string',
                'subkey2.3' => new Varien_Object(array('test_key' => 'test_value'))
            ),
            'key3' => 5
        );
        $this->_object->setData($data);

        $this->assertEquals($data, $this->_object->getData());
        $this->assertEquals('value1', $this->_object->getData('key1'));
        $this->assertEquals('value2.1', $this->_object->getData('key2/subkey2.1'));
        $this->assertEquals('value2.1', $this->_object->getData('key2', 'subkey2.1'));
        $this->assertEquals('string', $this->_object->getData('key2/subkey2.2', 1));
        $this->assertEquals('test_value', $this->_object->getData('key2/subkey2.3', 'test_key'));
        $this->assertNull($this->_object->getData('key3', 'test_key'));
    }

    public function testGetDataByPath()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => array(
                'subkey2.1' => 'value2.1',
                'subkey2.2' => 'multiline
string',
                'subkey2.3' => new Varien_Object(array('test_key' => 'test_value'))
            ),
        ));
        $this->assertEquals('value1', $this->_object->getDataByPath('key1'));
        $this->assertEquals('value2.1', $this->_object->getDataByPath('key2/subkey2.1'));
        $this->assertEquals('test_value', $this->_object->getDataByPath('key2/subkey2.3/test_key'));
        $this->assertNull($this->_object->getDataByPath('empty'));
        $this->assertNull($this->_object->getDataByPath('empty/path'));
    }

    public function testGetDataByKey()
    {
        $this->_object->setData(array('key' => 'value'));
        $this->assertEquals('value', $this->_object->getDataByKey('key'));
        $this->assertNull($this->_object->getDataByKey('empty'));
    }

    /**
     * Tests Varien_Object->setDataUsingMethod()
     */
    public function testSetGetDataUsingMethod()
    {
        $mock = $this->getMock('Varien_Object', array('setTestData', 'getTestData'));
        $mock->expects($this->once())
            ->method('setTestData')
            ->with($this->equalTo('data'));
        $mock->expects($this->once())
            ->method('getTestData');

        $mock->setDataUsingMethod('test_data', 'data');
        $mock->getDataUsingMethod('test_data');
    }

    /**
     * Tests Varien_Object->getDataSetDefault()
     */
    public function testGetDataSetDefault()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => null,
        ));
        $this->assertEquals('value1', $this->_object->getDataSetDefault('key1', 'default'));
        $this->assertEquals(null, $this->_object->getDataSetDefault('key2', 'default'));
        $this->assertEquals('default', $this->_object->getDataSetDefault('key3', 'default'));
    }

    /**
     * Tests Varien_Object->hasData()
     */
    public function testHasData()
    {
        $this->assertFalse($this->_object->hasData());
        $this->assertFalse($this->_object->hasData('key'));
        $this->_object->setData('key', 'value');
        $this->assertTrue($this->_object->hasData('key'));
    }

    /**
     * Tests Varien_Object->toArray()
     */
    public function testToArray()
    {
        $this->assertEquals(array(), $this->_object->toArray());
        $this->assertEquals(array('key' => null), $this->_object->toArray(array('key')));
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));
        $this->assertEquals(array('key1' => 'value1'), $this->_object->toArray(array('key1')));
        $this->assertEquals(array('key2' => 'value2'), $this->_object->__toArray(array('key2')));
    }

    /**
     * Tests Varien_Object->toXml()
     */
    public function testToXml()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));
        $xml = '<item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->_object->toXml());

        $xml = '<item>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->_object->toXml(array('key2')));

        $xml = '<my_item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</my_item>
';
        $this->assertEquals($xml, $this->_object->toXml(array(), 'my_item'));

        $xml = '<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
';
        $this->assertEquals($xml, $this->_object->toXml(array(), false));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->_object->toXml(array(), 'item', true));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1>value1</key1>
<key2>value2</key2>
</item>
';
        $this->assertEquals($xml, $this->_object->__toXml(array(), 'item', true, false));
    }

    /**
     * Tests Varien_Object->toJson()
     */
    public function testToJson()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));
        $this->assertEquals('{"key1":"value1","key2":"value2"}', $this->_object->toJson());
        $this->assertEquals('{"key1":"value1"}', $this->_object->toJson(array('key1')));
        $this->assertEquals('{"key1":"value1","key":null}', $this->_object->__toJson(array('key1', 'key')));
    }
    /**
     * Tests Varien_Object->toString()
     */
    public function testToString()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));
        $this->assertEquals('value1, value2', $this->_object->toString());
        $this->assertEquals('test value1 with value2', $this->_object->toString('test {{key1}} with {{key2}}'));
    }

    /**
     * Tests Varien_Object->__call()
     *
     * @expectedException Varien_Exception
     */
    public function test__call()
    {
        $this->_object->setData(array('key' => 'value'));
        $this->_object->setTest('test');
        $this->assertEquals('test', $this->_object->getData('test'));

        $this->assertEquals($this->_object->getData('test'), $this->_object->getTest());

        $this->assertTrue($this->_object->hasTest());
        $this->_object->unsTest();
        $this->assertNull($this->_object->getData('test'));

        $this->_object->testTest();
    }

    /**
     * Tests Varien_Object->__get()
     */
    public function test__get__set()
    {
        $this->_object->test = 'test';
        $this->assertEquals('test', $this->_object->test);

        $this->_object->testTest = 'test';
        $this->assertEquals('test', $this->_object->testTest);
    }

    /**
     * Tests Varien_Object->isEmpty()
     */
    public function testIsEmpty()
    {
        $this->assertTrue($this->_object->isEmpty());
        $this->_object->setData('test', 'test');
        $this->assertFalse($this->_object->isEmpty());
    }

    /**
     * Tests Varien_Object->serialize()
     */
    public function testSerialize()
    {
        $this->_object->setData(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));
        $this->assertEquals('key1="value1" key2="value2"', $this->_object->serialize());
        $this->assertEquals(
            'key1:\'value1\'_key2:\'value2\'',
            $this->_object->serialize(array('key', 'key1', 'key2'), ':', '_', '\'')
        );
    }

    /**
     * Tests Varien_Object->setOrigData()
     */
    public function testOrigData()
    {
        $data = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );
        $this->_object->setData($data);
        $this->_object->setOrigData();
        $this->_object->setData('key1', 'test');
        $this->assertTrue($this->_object->dataHasChangedFor('key1'));
        $this->assertEquals($data, $this->_object->getOrigData());

        $this->_object->setOrigData('key1', 'test');
        $this->assertEquals('test', $this->_object->getOrigData('key1'));
    }

    /**
     * Tests Varien_Object->setDataChanges()
     */
    public function testSetDataChanges()
    {
        $this->assertFalse($this->_object->hasDataChanges());
        $this->_object->setDataChanges(true);
        $this->assertTrue($this->_object->hasDataChanges());
    }

    /**
     * Tests Varien_Object->debug()
     */
    public function testDebug()
    {
        $data = array(
            'key1' => 'value1',
            'key2' => array('test'),
            'key3' => $this->_object
        );
        $this->_object->setData($data);

        $debug = $data;
        unset($debug['key3']);
        $debug['key3 (Varien_Object)'] = '*** RECURSION ***';
        $this->assertEquals($debug, $this->_object->debug());
    }

    /**
     * Tests Varien_Object->offsetSet()
     */
    public function testOffset()
    {
        $this->_object->offsetSet('key1', 'value1');
        $this->assertTrue($this->_object->offsetExists('key1'));
        $this->assertFalse($this->_object->offsetExists('key2'));

        $this->assertEquals('value1', $this->_object->offsetGet('key1'));
        $this->assertNull($this->_object->offsetGet('key2'));
        $this->_object->offsetUnset('key1');
        $this->assertFalse($this->_object->offsetExists('key1'));
    }
}

