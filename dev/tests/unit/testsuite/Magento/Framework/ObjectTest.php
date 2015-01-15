<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Object test case.
 */
namespace Magento\Framework;

use PHPUnit_Framework_TestCase;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object
     */
    private $_object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_object = new \Magento\Framework\Object();
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
     * Tests \Magento\Framework\Object->__construct()
     */
    public function testConstruct()
    {
        $object = new \Magento\Framework\Object();
        $this->assertEquals([], $object->getData());

        $data = ['test' => 'test'];
        $object = new \Magento\Framework\Object($data);
        $this->assertEquals($data, $object->getData());
    }

    /**
     * Tests \Magento\Framework\Object->isDeleted()
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
     * Tests \Magento\Framework\Object->hasDataChanges()
     */
    public function testHasDataChanges()
    {
        $this->assertFalse($this->_object->hasDataChanges());
        $this->_object->setData('key', 'value');
        $this->assertTrue($this->_object->hasDataChanges(), 'Data changed');

        $object = new \Magento\Framework\Object(['key' => 'value']);
        $object->setData('key', 'value');
        $this->assertFalse($object->hasDataChanges(), 'Data not changed');

        $object->setData(['key' => 'value']);
        $this->assertFalse($object->hasDataChanges(), 'Data not changed (array)');

        $object = new \Magento\Framework\Object();
        $object->unsetData();
        $this->assertFalse($object->hasDataChanges(), 'Unset data');

        $object = new \Magento\Framework\Object(['key' => null]);
        $object->setData('key', null);
        $this->assertFalse($object->hasDataChanges(), 'Null data');
    }

    /**
     * Tests \Magento\Framework\Object->getId()
     */
    public function testSetGetId()
    {
        $this->_object->setId('test');
        $this->assertEquals('test', $this->_object->getId());
    }

    public function testSetGetIdFieldName()
    {
        $name = 'entity_id_custom';
        $this->_object->setIdFieldName($name);
        $this->assertEquals($name, $this->_object->getIdFieldName());
    }

    /**
     * Tests \Magento\Framework\Object->addData()
     */
    public function testAddData()
    {
        $this->_object->addData(['test' => 'value']);
        $this->assertEquals('value', $this->_object->getData('test'));

        $this->_object->addData(['test' => 'value1']);
        $this->assertEquals('value1', $this->_object->getData('test'));

        $this->_object->addData(['test2' => 'value2']);
        $this->assertEquals(['test' => 'value1', 'test2' => 'value2'], $this->_object->getData());
    }

    /**
     * Tests \Magento\Framework\Object->setData()
     */
    public function testSetData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 3];
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
     * Tests \Magento\Framework\Object->unsetData()
     */
    public function testUnsetData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 3, 'key4' => 4];
        $this->_object->setData($data);

        $this->_object->unsetData('key1');
        unset($data['key1']);
        $this->assertEquals($data, $this->_object->getData());

        $this->_object->unsetData(['key2', 'key3']);
        unset($data['key2']);
        unset($data['key3']);
        $this->assertEquals($data, $this->_object->getData());

        $this->_object->unsetData();
        $this->assertEquals([], $this->_object->getData());
    }

    /**
     * Tests \Magento\Framework\Object->getData()
     */
    public function testGetData()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'subkey2.1' => 'value2.1',
                'subkey2.2' => 'multiline
string',
                'subkey2.3' => new \Magento\Framework\Object(['test_key' => 'test_value']),
            ],
            'key3' => 5,
        ];
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
        $this->_object->setData(
            [
                'key1' => 'value1',
                'key2' => [
                    'subkey2.1' => 'value2.1',
                    'subkey2.2' => 'multiline
string',
                    'subkey2.3' => new \Magento\Framework\Object(['test_key' => 'test_value']),
                ],
            ]
        );
        $this->assertEquals('value1', $this->_object->getDataByPath('key1'));
        $this->assertEquals('value2.1', $this->_object->getDataByPath('key2/subkey2.1'));
        $this->assertEquals('test_value', $this->_object->getDataByPath('key2/subkey2.3/test_key'));
        $this->assertNull($this->_object->getDataByPath('empty'));
        $this->assertNull($this->_object->getDataByPath('empty/path'));
    }

    public function testGetDataByKey()
    {
        $this->_object->setData(['key' => 'value']);
        $this->assertEquals('value', $this->_object->getDataByKey('key'));
        $this->assertNull($this->_object->getDataByKey('empty'));
    }

    /**
     * Tests \Magento\Framework\Object->setDataUsingMethod()
     */
    public function testSetGetDataUsingMethod()
    {
        $mock = $this->getMock('Magento\Framework\Object', ['setTestData', 'getTestData']);
        $mock->expects($this->once())->method('setTestData')->with($this->equalTo('data'));
        $mock->expects($this->once())->method('getTestData');

        $mock->setDataUsingMethod('test_data', 'data');
        $mock->getDataUsingMethod('test_data');
    }

    /**
     * Tests \Magento\Framework\Object->getDataSetDefault()
     */
    public function testGetDataSetDefault()
    {
        $this->_object->setData(['key1' => 'value1', 'key2' => null]);
        $this->assertEquals('value1', $this->_object->getDataSetDefault('key1', 'default'));
        $this->assertEquals(null, $this->_object->getDataSetDefault('key2', 'default'));
        $this->assertEquals('default', $this->_object->getDataSetDefault('key3', 'default'));
    }

    /**
     * Tests \Magento\Framework\Object->hasData()
     */
    public function testHasData()
    {
        $this->assertFalse($this->_object->hasData());
        $this->assertFalse($this->_object->hasData('key'));
        $this->_object->setData('key', 'value');
        $this->assertTrue($this->_object->hasData('key'));
    }

    /**
     * Tests \Magento\Framework\Object->toArray()
     */
    public function testToArray()
    {
        $this->assertEquals([], $this->_object->toArray());
        $this->assertEquals(['key' => null], $this->_object->toArray(['key']));
        $this->_object->setData(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals(['key1' => 'value1'], $this->_object->toArray(['key1']));
        $this->assertEquals(['key2' => 'value2'], $this->_object->convertToArray(['key2']));
    }

    /**
     * Tests \Magento\Framework\Object->toXml()
     */
    public function testToXml()
    {
        $this->_object->setData(['key1' => 'value1', 'key2' => 'value2']);
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
        $this->assertEquals($xml, $this->_object->toXml(['key2']));

        $xml = '<my_item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</my_item>
';
        $this->assertEquals($xml, $this->_object->toXml([], 'my_item'));

        $xml = '<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
';
        $this->assertEquals($xml, $this->_object->toXml([], false));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->_object->toXml([], 'item', true));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1>value1</key1>
<key2>value2</key2>
</item>
';
        $this->assertEquals($xml, $this->_object->convertToXml([], 'item', true, false));
    }

    /**
     * Tests \Magento\Framework\Object->toJson()
     */
    public function testToJson()
    {
        $this->_object->setData(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals('{"key1":"value1","key2":"value2"}', $this->_object->toJson());
        $this->assertEquals('{"key1":"value1"}', $this->_object->toJson(['key1']));
        $this->assertEquals('{"key1":"value1","key":null}', $this->_object->convertToJson(['key1', 'key']));
    }

    /**
     * Tests \Magento\Framework\Object->toString()
     */
    public function testToString()
    {
        $this->_object->setData(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals('value1, value2', $this->_object->toString());
        $this->assertEquals('test value1 with value2', $this->_object->toString('test {{key1}} with {{key2}}'));
    }

    /**
     * Tests \Magento\Framework\Object->__call()
     *
     * @expectedException \Magento\Framework\Exception
     */
    public function testCall()
    {
        $this->_object->setData(['key' => 'value']);
        $this->_object->setTest('test');
        $this->assertEquals('test', $this->_object->getData('test'));

        $this->assertEquals($this->_object->getData('test'), $this->_object->getTest());

        $this->assertTrue($this->_object->hasTest());
        $this->_object->unsTest();
        $this->assertNull($this->_object->getData('test'));

        $this->_object->testTest();
    }

    /**
     * Tests \Magento\Framework\Object->__get()
     */
    public function testGetSet()
    {
        $this->_object->test = 'test';
        $this->assertEquals('test', $this->_object->test);

        $this->_object->testTest = 'test';
        $this->assertEquals('test', $this->_object->testTest);
    }

    /**
     * Tests \Magento\Framework\Object->isEmpty()
     */
    public function testIsEmpty()
    {
        $this->assertTrue($this->_object->isEmpty());
        $this->_object->setData('test', 'test');
        $this->assertFalse($this->_object->isEmpty());
    }

    /**
     * Tests \Magento\Framework\Object->serialize()
     */
    public function testSerialize()
    {
        $this->_object->setData(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals('key1="value1" key2="value2"', $this->_object->serialize());
        $this->assertEquals(
            'key1:\'value1\'_key2:\'value2\'',
            $this->_object->serialize(['key', 'key1', 'key2'], ':', '_', '\'')
        );
    }

    /**
     * Tests \Magento\Framework\Object->setOrigData()
     */
    public function testOrigData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $this->_object->setData($data);
        $this->_object->setOrigData();
        $this->_object->setData('key1', 'test');
        $this->assertTrue($this->_object->dataHasChangedFor('key1'));
        $this->assertEquals($data, $this->_object->getOrigData());

        $this->_object->setOrigData('key1', 'test');
        $this->assertEquals('test', $this->_object->getOrigData('key1'));
    }

    /**
     * Tests \Magento\Framework\Object->setDataChanges()
     */
    public function testSetDataChanges()
    {
        $this->assertFalse($this->_object->hasDataChanges());
        $this->_object->setDataChanges(true);
        $this->assertTrue($this->_object->hasDataChanges());
    }

    /**
     * Tests \Magento\Framework\Object->debug()
     */
    public function testDebug()
    {
        $data = ['key1' => 'value1', 'key2' => ['test'], 'key3' => $this->_object];
        $this->_object->setData($data);

        $debug = $data;
        unset($debug['key3']);
        $debug['key3 (Magento\Framework\Object)'] = '*** RECURSION ***';
        $this->assertEquals($debug, $this->_object->debug());
    }

    /**
     * Tests \Magento\Framework\Object->offsetSet()
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

    /**
     * Tests _underscore method directly
     *
     * @dataProvider underscoreDataProvider
     */
    public function testUnderscore($input, $expectedOutput)
    {
        $refObject = new \ReflectionObject($this->_object);
        $refMethod = $refObject->getMethod('_underscore');
        $refMethod->setAccessible(true);
        $output = $refMethod->invoke($this->_object, $input);
        $this->assertEquals($expectedOutput, $output);
    }

    public function underscoreDataProvider()
    {
        return [
            'Test 1' => ['Stone1Color', 'stone_1_color'],
            'Test 2' => ['StoneColor', 'stone_color'],
            'Test 3' => ['StoneToXml', 'stone_to_xml'],
            'Test 4' => ['1StoneColor', '1_stone_color'],
            'Test 5' => ['getCcLast4', 'get_cc_last_4'],
            'Test 6' => ['99Bottles', '99_bottles'],
            'Test 7' => ['XApiLogin', 'x_api_login']
        ];
    }
}
