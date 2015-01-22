<?php
/** 
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute;
 
class AbstractAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptionWhenOptionsAreSet()
    {
        $model = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [],
            '',
            false,
            false,
            true,
            [
                'getData', 'usesSource', 'getSource', 'convertToObjects'
            ]
        );

        $model->expects($this->once())
            ->method('getData')
            ->with(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::OPTIONS)
            ->willReturn(['options']);
        $model->expects($this->never())->method('usesSource');
        $model->expects($this->once())
            ->method('convertToObjects')
            ->with(['options'])
            ->willReturn('expected value');


        $this->assertEquals('expected value', $model->getOptions());
    }

    public function testGetOptionWhenOptionsAreEmptyWithoutSource()
    {
        $model = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [],
            '',
            false,
            false,
            true,
            [
                'getData', 'usesSource', 'getSource', 'convertToObjects'
            ]
        );

        $model->expects($this->once())
            ->method('getData')
            ->with(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::OPTIONS)
            ->willReturn([]);
        $model->expects($this->once())->method('usesSource')->willReturn(false);
        $model->expects($this->never())->method('getSource');
        $model->expects($this->once())
            ->method('convertToObjects')
            ->with([])
            ->willReturn('expected value');

        $this->assertEquals('expected value', $model->getOptions());
    }

    public function testGetOptionWhenOptionsAreEmptyWithSource()
    {
        $model = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [],
            '',
            false,
            false,
            true,
            [
                'getData', 'usesSource', 'getSource', 'convertToObjects', 'getAllOptions'
            ]
        );

        $model->expects($this->once())
            ->method('getData')
            ->with(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::OPTIONS)
            ->willReturn([]);
        $model->expects($this->once())->method('usesSource')->willReturn(true);
        $model->expects($this->once())->method('getSource')->willReturnSelf();
        $model->expects($this->once())->method('getAllOptions')->willReturn(['source value']);
        $model->expects($this->once())
            ->method('convertToObjects')
            ->with(['source value'])
            ->willReturn('expected value');

        $this->assertEquals('expected value', $model->getOptions());
    }

    public function testConvertToObjects()
    {
        $dataBuilderMock = $this->getMock(
            'Magento\Eav\Api\Data\AttributeOptionDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Entity\Attribute',
            [
                'optionDataBuilder' => $dataBuilderMock,
                'data' => [
                    \Magento\Eav\Api\Data\AttributeInterface::OPTIONS => [['some value']]
                ]

            ]
        );
        $dataBuilderMock->expects($this->once())->method('populateWithArray')->with(['some value'])->willReturnSelf();
        $dataBuilderMock->expects($this->once())->method('create')->willReturn('Expected value');

        $this->assertEquals(['Expected value'], $model->getOptions());
    }

    public function testGetValidationRulesWhenRuleIsArray()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Entity\Attribute',
            [
                'data' => [
                    \Magento\Eav\Api\Data\AttributeInterface::VALIDATE_RULES => ['some value']
                ]

            ]
        );

        $this->assertEquals(['some value'], $model->getValidationRules());
    }

    public function testGetValidationRulesWhenRuleIsSerialized()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $rule = 'some value';
        $model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Entity\Attribute',
            [
                'data' => [
                    \Magento\Eav\Api\Data\AttributeInterface::VALIDATE_RULES => serialize($rule)
                ]

            ]
        );

        $this->assertEquals($rule, $model->getValidationRules());
    }

    public function testGetValidationRulesWhenRuleIsEmpty()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Entity\Attribute',
            [
                'data' => [
                    \Magento\Eav\Api\Data\AttributeInterface::VALIDATE_RULES => null
                ]

            ]
        );

        $this->assertEquals([], $model->getValidationRules());
    }
}
