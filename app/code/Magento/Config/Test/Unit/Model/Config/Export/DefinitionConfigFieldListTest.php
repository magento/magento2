<?php
/**
 * Created by PhpStorm.
 * User: ymiroshnychenko
 * Date: 3/9/2017
 * Time: 6:55 PM
 */

namespace Magento\Config\Test\Unit\Model\Config\Export;


use Magento\Config\Model\Config\Export\DefinitionConfigFieldList;

class DefinitionConfigFieldListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $sensitiveFieldList
     * @param array $envSpecificVariableList
     * @param $configField
     * @param $type
     * @param $expectedResult
     * @dataProvider dataProviderToTestBelongsTo
     */
    public function testBelongsTo(
        array $sensitiveFieldList,
        array $envSpecificVariableList,
        $configField,
        $type,
        $expectedResult
    ) {
        $definitionConfigFieldList = new DefinitionConfigFieldList($sensitiveFieldList, $envSpecificVariableList);
        $result = $definitionConfigFieldList->belongsTo($configField, $type);
        if ($expectedResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function testIsPresent()
    {
        $definitionConfigFieldList = new DefinitionConfigFieldList(
            [
                'some/config/field1' => '',
                'some/config/field2' => '0',
                'some/config/field3' => '1',
                'some/config/field4' => '1',
                'some/config/field6' => '0',
            ],
            [
                'some/config/field4' => '0',
                'some/config/field5' => 0,
                'some/config/field6' => 1,
                'some/config/field7' => 1,
                'some/config/field8' => 1,
            ]
        );

        $this->assertFalse($definitionConfigFieldList->isPresent(''));
        $this->assertFalse($definitionConfigFieldList->isPresent('some/wrong/config/field'));

        $this->assertFalse($definitionConfigFieldList->isPresent('some/config/field1'));
        $this->assertFalse($definitionConfigFieldList->isPresent('some/config/field2'));
        $this->assertFalse($definitionConfigFieldList->isPresent('some/config/field5'));

        $this->assertTrue($definitionConfigFieldList->isPresent('some/config/field3'));
        $this->assertTrue($definitionConfigFieldList->isPresent('some/config/field6'));
        $this->assertTrue($definitionConfigFieldList->isPresent('some/config/field4'));
        $this->assertTrue($definitionConfigFieldList->isPresent('some/config/field6'));
    }

    /**
     * @return array
     */
    public function dataProviderToTestBelongsTo()
    {
        return [
            [
                'sensitiveFieldList' => [],
                'envSpecificVariableList' => [],
                'configField' => '',
                'type' => '',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'envSpecificVariableList' => ['some/env/specific/variable1' => '1'],
                'configField' => 'some/wrong/field',
                'type' => 'someWrongType',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'envSpecificVariableList' => ['some/env/specific/variable1' => '1'],
                'configField' => 'some/sensitive/field1',
                'type' => 'someWrongType',
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'envSpecificVariableList' => ['some/env/specific/variable1' => '1'],
                'configField' => 'some/wrong/field',
                'type' => DefinitionConfigFieldList::SENSITIVE_FIELDS,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'envSpecificVariableList' => ['some/env/specific/variable1' => '1'],
                'configField' => 'some/env/specific/variable1',
                'type' => DefinitionConfigFieldList::ENV_SPECIFIC_VARIABLES,
                'expectedResult' => true,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'envSpecificVariableList' => ['some/env/specific/variable1' => '1'],
                'configField' => 'some/env/specific/variable1',
                'type' => DefinitionConfigFieldList::SENSITIVE_FIELDS,
                'expectedResult' => false,
            ],
        ];
    }
}
