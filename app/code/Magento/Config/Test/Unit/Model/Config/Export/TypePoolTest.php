<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\TypePool;

class TypePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $sensitive
     * @param array $environment
     * @param $path
     * @param $type
     * @param $expectedResult
     * @dataProvider dataProviderToTestIsPresent
     */
    public function testIsPresent(
        array $sensitive,
        array $environment,
        $path,
        $type,
        $expectedResult
    ) {
        $typePool = new TypePool($sensitive, $environment);
        $this->assertSame($expectedResult, $typePool->isPresent($path, $type));
    }

    /**
     * @return array
     */
    public function dataProviderToTestIsPresent()
    {
        return [
            [
                'sensitiveFieldList' => [],
                'environmentFieldList' => [],
                'field' => '',
                'typeList' => '',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/wrong/field',
                'typeList' => 'someWrongType',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/sensitive/field1',
                'typeList' => 'someWrongType',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/wrong/field',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/environment/field1',
                'typeList' => TypePool::TYPE_ENVIRONMENT,
                'expectedResult' => true,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/environment/field1',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive-environment/field1' => '1'],
                'environmentFieldList' => ['some/sensitive-environment/field1' => '1'],
                'field' => 'some/sensitive-environment/field1',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'expectedResult' => true,
            ],
        ];
    }
}
