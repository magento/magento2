<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Type caster Test
 */
class TypeCasterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TypeCaster
     */
    private $model;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->serializer = $this->getMockBuilder(Json::class)
            ->getMock();

        $this->model = $objectManager->getObject(TypeCaster::class, ['serializer' => $this->serializer]);
    }

    /**
     * Checks type casting for different php data types.
     *
     * @param mixed $origValue
     * @param string $typeToCast
     * @param mixed $expectedValue
     * @dataProvider typeCastValueProvider
     */
    public function testCastValues($origValue, $typeToCast, $expectedValue)
    {
        $this->serializer->expects(self::never())
            ->method('serialize');

        $value = $this->model->castValueToType($origValue, $typeToCast);
        self::assertEquals($expectedValue, $value);
    }

    /**
     * Checks a test case when array should be converted to json string representation.
     *
     * @covers \Magento\Framework\Reflection\TypeCaster::castValueToType
     * @param array $origValue
     * @param string $typeToCast
     * @param string $expected
     * @dataProvider arraysDataProvider
     */
    public function testCastValueToType(array $origValue, $typeToCast, $expected)
    {
        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with(self::equalTo($origValue))
            ->willReturn(json_encode($origValue));

        $actual = $this->model->castValueToType($origValue, $typeToCast);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function typeCastValueProvider()
    {
        return [
            'null' => [null, 'int', null],
            'int' => ['1', 'int', 1],
            'integer' => ['1', 'integer', 1],
            'string' => ['1', 'string', '1'],
            'bool 0' => ['0', 'bool', false],
            'bool 1' => ['1', 'bool', true],
            'boolean 0' => ['0', 'boolean', false],
            'boolean 1' => ['1', 'boolean', true],
            'true' => ['1', 'true', true],
            'false' => ['0', 'false', false],
            'float' => ['1.03', 'float', 1.03],
            'double' => ['1.30', 'double', 1.30],
            'array of objects' => [[1, 2.0, '3b'], \stdClass::class, [1, 2.0, '3b']],
            'array of interfaces' => [['a', 23, '1.a'], \Traversable::class, ['a', 23, '1.a']],
        ];
    }

    /**
     * Gets list of variations for testing array encoding.
     *
     * @return array
     */
    public function arraysDataProvider()
    {
        return [
            [['type' => 'VI', 'masked' => 1111], 'string', '{"type":"VI","masked":1111}'],
            [['status' => 'processing', 'parent_id' => 2], 'int', '{"status":"processing","parent_id":2}'],
            [['parent' => ['children' => [1, 2]], 'node' => 2], 'mixed', '{"parent":{"children":[1,2]},"node":2}'],
        ];
    }
}
