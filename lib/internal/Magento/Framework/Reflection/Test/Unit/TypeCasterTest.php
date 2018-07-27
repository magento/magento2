<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\TypeCaster;

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
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Reflection\TypeCaster');
    }

    /**
     * @param mixed $origValue
     * @param string $typeToCast
     * @param mixed $expectedValue
     * @dataProvider typeCastValueProvider
     */
    public function testCastValues($origValue, $typeToCast, $expectedValue)
    {
        $value = $this->model->castValueToType($origValue, $typeToCast);
        $this->assertTrue($value === $expectedValue);
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
        ];
    }
}
