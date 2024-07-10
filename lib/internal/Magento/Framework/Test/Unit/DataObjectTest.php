<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * \Magento\Framework\DataObject test case.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class DataObjectTest extends TestCase
{
    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dataObject = new DataObject();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->dataObject = null;
        parent::tearDown();
    }

    /**
     * Tests \Magento\Framework\DataObject->__construct()
     */
    public function testConstruct()
    {
        $object = new DataObject();
        $this->assertEquals([], $object->getData());

        $data = ['test' => 'test'];
        $object = new DataObject($data);
        $this->assertEquals($data, $object->getData());
    }

    /**
     * Tests \Magento\Framework\DataObject->addData()
     */
    public function testAddData()
    {
        $this->dataObject->addData(['test' => 'value']);
        $this->assertEquals('value', $this->dataObject->getData('test'));

        $this->dataObject->addData(['test' => 'value1']);
        $this->assertEquals('value1', $this->dataObject->getData('test'));

        $this->dataObject->addData(['test2' => 'value2']);
        $this->assertEquals(['test' => 'value1', 'test2' => 'value2'], $this->dataObject->getData());
    }

    /**
     * Tests \Magento\Framework\DataObject->setData()
     */
    public function testSetData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 3];
        $this->dataObject->setData($data);
        $this->assertEquals($data, $this->dataObject->getData());

        $data['key1'] = 1;
        $this->dataObject->setData('key1', 1);
        $this->assertEquals($data, $this->dataObject->getData());

        $this->dataObject->setData('key1');
        $data['key1'] = null;
        $this->assertEquals($data, $this->dataObject->getData());
    }

    /**
     * Tests \Magento\Framework\DataObject->unsetData()
     */
    public function testUnsetData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 3, 'key4' => 4];
        $this->dataObject->setData($data);

        $this->dataObject->unsetData('key1');
        unset($data['key1']);
        $this->assertEquals($data, $this->dataObject->getData());

        $this->dataObject->unsetData(['key2', 'key3']);
        unset($data['key2']);
        unset($data['key3']);
        $this->assertEquals($data, $this->dataObject->getData());

        $this->dataObject->unsetData();
        $this->assertEquals([], $this->dataObject->getData());
    }

    /**
     * Tests \Magento\Framework\DataObject->getData()
     */
    public function testGetData()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'subkey2.1' => 'value2.1',
                'subkey2.2' => 'multiline' . PHP_EOL . 'string',
                'subkey2.3' => new DataObject(['test_key' => 'test_value']),
            ],
            'key3' => 5,
        ];
        foreach ($data as $key => $value) {
            $this->dataObject->setData($key, $value);
        }
        $this->assertEquals($data, $this->dataObject->getData());
        $this->assertEquals('value1', $this->dataObject->getData('key1'));
        $this->assertEquals('value2.1', $this->dataObject->getData('key2/subkey2.1'));
        $this->assertEquals('value2.1', $this->dataObject->getData('key2', 'subkey2.1'));
        $this->assertEquals('string', $this->dataObject->getData('key2/subkey2.2', 1));
        $this->assertEquals('test_value', $this->dataObject->getData('key2/subkey2.3', 'test_key'));
        $this->assertNull($this->dataObject->getData('key3', 'test_key'));
    }

    public function testGetDataByPath()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'subkey2.1' => 'value2.1',
                'subkey2.2' => 'multiline
string',
                'subkey2.3' => new DataObject(['test_key' => 'test_value']),
            ],
        ];
        foreach ($data as $key => $value) {
            $this->dataObject->setData($key, $value);
        }
        $this->assertEquals('value1', $this->dataObject->getDataByPath('key1'));
        $this->assertEquals('value2.1', $this->dataObject->getDataByPath('key2/subkey2.1'));
        $this->assertEquals('test_value', $this->dataObject->getDataByPath('key2/subkey2.3/test_key'));
        $this->assertNull($this->dataObject->getDataByPath('empty'));
        $this->assertNull($this->dataObject->getDataByPath('empty/path'));
    }

    public function testGetDataByKey()
    {
        $this->dataObject->setData('key', 'value');
        $this->assertEquals('value', $this->dataObject->getDataByKey('key'));
        $this->assertNull($this->dataObject->getDataByKey('empty'));
    }

    /**
     * Tests \Magento\Framework\DataObject->setDataUsingMethod()
     */
    public function testSetGetDataUsingMethod()
    {
        $mock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setTestData', 'getTestData'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('setTestData')->with('data');
        $mock->expects($this->once())->method('getTestData');

        $mock->setDataUsingMethod('test_data', 'data');
        $mock->getDataUsingMethod('test_data');
    }

    /**
     * Test documenting current behaviour of getDataUsingMethod
     * _underscore assumes an underscore before any digit
     */
    public function testGetDataUsingMethodWithoutUnderscore()
    {
        $this->dataObject->setData('key_1', 'value1');
        $this->assertTrue($this->dataObject->hasData('key_1'));
        $this->assertEquals('value1', $this->dataObject->getDataUsingMethod('key_1'));

        $this->dataObject->setData('key2', 'value2');
        $this->assertEquals('value2', $this->dataObject->getData('key2'));
        $this->assertNull($this->dataObject->getKey2());
        $this->assertNull($this->dataObject->getDataUsingMethod('key2'));
    }

    /**
     * Tests \Magento\Framework\DataObject->hasData()
     */
    public function testHasData()
    {
        $this->assertFalse($this->dataObject->hasData());
        $this->assertFalse($this->dataObject->hasData('key'));
        $this->dataObject->setData('key', 'value');
        $this->assertTrue($this->dataObject->hasData('key'));
    }

    /**
     * Tests \Magento\Framework\DataObject->toArray()
     */
    public function testToArray()
    {
        $this->assertEquals([], $this->dataObject->toArray());
        $this->assertEquals(['key' => null], $this->dataObject->toArray(['key']));
        $this->dataObject->setData('key1', 'value1');
        $this->dataObject->setData('key2', 'value2');
        $this->assertEquals(['key1' => 'value1'], $this->dataObject->toArray(['key1']));
        $this->assertEquals(['key2' => 'value2'], $this->dataObject->convertToArray(['key2']));
    }

    /**
     * Tests \Magento\Framework\DataObject->toXml()
     */
    public function testToXml()
    {
        $this->dataObject->setData('key1', 'value1');
        $this->dataObject->setData('key2', 'value2');
        $xml = '<item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->dataObject->toXml());

        $xml = '<item>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->dataObject->toXml(['key2']));

        $xml = '<my_item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</my_item>
';
        $this->assertEquals($xml, $this->dataObject->toXml([], 'my_item'));

        $xml = '<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
';
        $this->assertEquals($xml, $this->dataObject->toXml([], false));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1><![CDATA[value1]]></key1>
<key2><![CDATA[value2]]></key2>
</item>
';
        $this->assertEquals($xml, $this->dataObject->toXml([], 'item', true));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<item>
<key1>value1</key1>
<key2>value2</key2>
</item>
';
        $this->assertEquals($xml, $this->dataObject->convertToXml([], 'item', true, false));
    }

    /**
     * Tests \Magento\Framework\DataObject->toJson()
     */
    public function testToJson()
    {
        $this->dataObject->setData('key1', 'value1');
        $this->dataObject->setData('key2', 'value2');
        $this->assertEquals('{"key1":"value1","key2":"value2"}', $this->dataObject->toJson());
        $this->assertEquals('{"key1":"value1"}', $this->dataObject->toJson(['key1']));
        $this->assertEquals('{"key1":"value1","key":null}', $this->dataObject->convertToJson(['key1', 'key']));
    }

    /**
     * Tests \Magento\Framework\DataObject->toString()
     */
    public function testToString()
    {
        $this->dataObject->setData('key1', 'value1');
        $this->dataObject->setData('key2', 'value2');
        $this->assertEquals('value1, value2', $this->dataObject->toString());
        $this->assertEquals('test value1 with value2', $this->dataObject->toString('test {{key1}} with {{key2}}'));
    }

    /**
     * Tests \Magento\Framework\DataObject->__call()
     */
    public function testCall()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->dataObject->setData('key', 'value');
        $this->dataObject->setTest('test');
        $this->assertEquals('test', $this->dataObject->getData('test'));

        $this->assertEquals($this->dataObject->getData('test'), $this->dataObject->getTest());

        $this->assertTrue($this->dataObject->hasTest());
        $this->dataObject->unsTest();
        $this->assertNull($this->dataObject->getData('test'));

        $this->dataObject->testTest();
    }

    /**
     * Tests \Magento\Framework\DataObject->__get()
     */
    public function testGetSet()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->dataObject->test = 'test';
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals('test', $this->dataObject->test);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->dataObject->testTest = 'test';
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals('test', $this->dataObject->testTest);
    }

    /**
     * Tests \Magento\Framework\DataObject->isEmpty()
     */
    public function testIsEmpty()
    {
        $this->assertTrue($this->dataObject->isEmpty());
        $this->dataObject->setData('test', 'test');
        $this->assertFalse($this->dataObject->isEmpty());
    }

    /**
     * Tests \Magento\Framework\DataObject->serialize()
     */
    public function testSerialize()
    {
        $this->dataObject->setData('key1', 'value1');
        $this->dataObject->setData('key2', 'value2');
        $this->assertEquals('key1="value1" key2="value2"', $this->dataObject->serialize());
        $this->assertEquals(
            'key1:\'value1\'_key2:\'value2\'',
            $this->dataObject->serialize(['key', 'key1', 'key2'], ':', '_', '\'')
        );
    }

    /**
     * Tests \Magento\Framework\DataObject->debug()
     */
    public function testDebug()
    {
        $data = ['key1' => 'value1', 'key2' => ['test'], 'key3' => $this->dataObject];
        foreach ($data as $key => $value) {
            $this->dataObject->setData($key, $value);
        }
        $debug = $data;
        unset($debug['key3']);
        $debug['key3 (Magento\Framework\DataObject)'] = '*** RECURSION ***';
        $this->assertEquals($debug, $this->dataObject->debug());
    }

    /**
     * Tests \Magento\Framework\DataObject->offsetSet()
     */
    public function testOffset()
    {
        $this->dataObject->offsetSet('key1', 'value1');
        $this->assertTrue($this->dataObject->offsetExists('key1'));
        $this->assertFalse($this->dataObject->offsetExists('key2'));

        $this->assertEquals('value1', $this->dataObject->offsetGet('key1'));
        $this->assertNull($this->dataObject->offsetGet('key2'));
        $this->dataObject->offsetUnset('key1');
        $this->assertFalse($this->dataObject->offsetExists('key1'));
    }

    /**
     * Tests _underscore method directly
     *
     * @dataProvider underscoreDataProvider
     */
    public function testUnderscore($input, $expectedOutput)
    {
        $refObject = new \ReflectionObject($this->dataObject);
        $refMethod = $refObject->getMethod('_underscore');
        $refMethod->setAccessible(true);
        $output = $refMethod->invoke($this->dataObject, $input);
        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * @return array
     */
    public function underscoreDataProvider()
    {
        return [
            'Test 1' => ['GetStone1Color', 'stone_1_color'],
            'Test 2' => ['SetStoneColor', 'stone_color'],
            'Test 3' => ['GetStoneToXml', 'stone_to_xml'],
            'Test 4' => ['Set1StoneColor', '1_stone_color'],
            'Test 5' => ['GetgetCcLast4', 'get_cc_last_4'],
            'Test 6' => ['Set99Bottles', '99_bottles'],
            'Test 7' => ['GetXApiLogin', 'x_api_login']
        ];
    }
}
