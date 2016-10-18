<?php
/** 
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;
 
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
        $attributeOptionMock = $this->getMock('\Magento\Eav\Api\Data\AttributeOptionInterface');
        $dataFactoryMock = $this->getMock(
            'Magento\Eav\Api\Data\AttributeOptionInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Entity\Attribute',
            [
                'optionDataFactory' => $dataFactoryMock,
                'dataObjectHelper' => $dataObjectHelperMock,
                'data' => [
                    \Magento\Eav\Api\Data\AttributeInterface::OPTIONS => [['some value']]
                ]

            ]
        );
        $dataObjectHelperMock->expects($this->once())->method('populateWithArray')
            ->with($attributeOptionMock, ['some value'], '\Magento\Eav\Api\Data\AttributeOptionInterface')
            ->willReturnSelf();
        $dataFactoryMock->expects($this->once())->method('create')->willReturn($attributeOptionMock);

        $this->assertEquals([$attributeOptionMock], $model->getOptions());
    }

    public function testGetValidationRulesWhenRuleIsArray()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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

    /**
     * @param bool $isEmpty
     * @param mixed $value
     * @param string $attributeType
     * @dataProvider attributeValueDataProvider
     */
    public function testIsValueEmpty($isEmpty, $value, $attributeType)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $model */
        $model = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [],
            '',
            false,
            false,
            true,
            [
                'getBackend'
            ]
        );
        $backendModelMock = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
            [],
            '',
            false,
            false,
            true,
            [
                'getType'
            ]
        );
        $backendModelMock->expects($this->any())->method('getType')->willReturn($attributeType);
        $model->expects($this->any())->method('getBackend')->willReturn($backendModelMock);
        $this->assertEquals($isEmpty, $model->isValueEmpty($value));
    }

    /**
     * @return array
     */
    public function attributeValueDataProvider()
    {
        return [
            [true, '', 'int'],
            [true, '', 'decimal'],
            [true, '', 'datetime'],
            [true, '', 'varchar'],
            [true, '', 'text'],
            [true, null, 'varchar'],
            [true, [], 'varchar'],
            [true, false, 'varchar'],
            [false, 'not empty value', 'varchar'],
            [false, false, 'int'],
        ];
    }
}
