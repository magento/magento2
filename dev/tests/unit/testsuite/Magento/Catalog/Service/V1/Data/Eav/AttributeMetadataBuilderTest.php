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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Data\Eav;

use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;

class AttributeMetadataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder|\PHPUnit_Framework_TestCase */
    protected $attributeMetadataBuilder;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\OptionBuilder */
    private $optionBuilderMock;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder */
    private $validationRuleBuilderMock;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder */
    private $frontendLabelBuilderMock;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\ValidationRule[] */
    private $validationRules;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\Option[] */
    private $optionRules;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel[] */
    private $frontendLabels;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        
        $this->optionBuilderMock =
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\OptionBuilder', [], [], '', false);

        $this->validationRuleBuilderMock =
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder', [], [], '', false);

        $this->frontendLabelBuilderMock =
            $this->getMock(
                'Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder', [], [], '', false
            );

        $this->validationRules = array(
            [0 => $this->getMock('Magento\Catalog\Service\V1\Data\Eav\ValidationRule', [], [], '', false)],
            [1 => $this->getMock('Magento\Catalog\Service\V1\Data\Eav\ValidationRule', [], [], '', false)]
        );

        $this->optionRules = array(
            [0 => $this->getMock('Magento\Catalog\Service\V1\Data\Eav\Option', [], [], '', false)],
            [1 => $this->getMock('Magento\Catalog\Service\V1\Data\Eav\Option', [], [], '', false)]
        );

        $this->frontendLabels = array(
            [
                0 => $this->getMock(
                    'Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel', [], [], '', false
                )
            ],
            [
                0 => $this->getMock(
                    'Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel', [], [], '', false
                )
            ],
        );

        $this->attributeMetadataBuilder = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder',
            [
                'optionBuilder' => $this->optionBuilderMock,
                'validationRuleBuilder' => $this->validationRuleBuilderMock,
                'frontendLabelBuilder' => $this->frontendLabelBuilderMock,
            ]
        );
    }

    /**
     * @dataProvider setValueDataProvider
     */
    public function testSetValue($method, $value, $getMethod)
    {
        $data = $this->attributeMetadataBuilder->$method($value)->create();
        $this->assertEquals($value, $data->$getMethod());
    }

    public function setValueDataProvider()
    {
        return array(
            ['setAttributeCode', 'code', 'getAttributeCode'],
            ['setFrontendInput', '<br>', 'getFrontendInput'],
            ['setValidationRules', $this->validationRules, 'getValidationRules'],
            ['setVisible', true, 'isVisible'],
            ['setRequired', true, 'isRequired'],
            ['setOptions', $this->optionRules, 'getOptions'],
            ['setUserDefined', false, 'isUserDefined'],
            ['setFrontendLabel', $this->frontendLabels, 'getFrontendLabel'],
            ['setFrontendClass', 'Class', 'getFrontendClass'],
            ['setNote', 'Text Note', 'getNote'],
        );
    }

    public function testPopulateWithArray()
    {
        $this->optionBuilderMock
            ->expects($this->at(0))
            ->method('populateWithArray')
            ->with($this->optionRules[0])
            ->will($this->returnSelf());
        $this->optionBuilderMock
            ->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($this->optionRules[0]));
        $this->optionBuilderMock
            ->expects($this->at(2))
            ->method('populateWithArray')
            ->with($this->optionRules[1])
            ->will($this->returnSelf());
        $this->optionBuilderMock
            ->expects($this->at(3))
            ->method('create')
            ->will($this->returnValue($this->optionRules[1]));

        $this->validationRuleBuilderMock
            ->expects($this->at(0))
            ->method('populateWithArray')
            ->with($this->validationRules[0])
            ->will($this->returnSelf());
        $this->validationRuleBuilderMock
            ->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($this->validationRules[0]));
        $this->validationRuleBuilderMock
            ->expects($this->at(2))
            ->method('populateWithArray')
            ->with($this->validationRules[1])
            ->will($this->returnSelf());
        $this->validationRuleBuilderMock
            ->expects($this->at(3))
            ->method('create')
            ->will($this->returnValue($this->validationRules[1]));

        $this->frontendLabelBuilderMock
            ->expects($this->at(0))
            ->method('populateWithArray')
            ->with($this->frontendLabels[0])
            ->will($this->returnSelf());
        $this->frontendLabelBuilderMock
            ->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($this->frontendLabels[0]));
        $this->frontendLabelBuilderMock
            ->expects($this->at(2))
            ->method('populateWithArray')
            ->with($this->frontendLabels[1])
            ->will($this->returnSelf());
        $this->frontendLabelBuilderMock
            ->expects($this->at(3))
            ->method('create')
            ->will($this->returnValue($this->frontendLabels[1]));

        $data = array(
            AttributeMetadata::OPTIONS => $this->optionRules,
            AttributeMetadata::VALIDATION_RULES => $this->validationRules,
            AttributeMetadata::FRONTEND_LABEL => $this->frontendLabels,
            'note' => $textNote = 'Text Note',
            'visible' => $visible = true,
            'some_key' => 'some_value',
        );

        $attributeData = $this->attributeMetadataBuilder->populateWithArray($data)->create();
        $this->assertEquals($textNote, $attributeData->getNote());
        $this->assertEquals($visible, $attributeData->isVisible());
        $this->assertEquals($data[AttributeMetadata::OPTIONS], $attributeData->getOptions());
        $this->assertEquals($data[AttributeMetadata::VALIDATION_RULES], $attributeData->getValidationRules());
        $this->assertEquals($data[AttributeMetadata::FRONTEND_LABEL], $attributeData->getFrontendLabel());
        $this->assertArrayNotHasKey('some_key', $attributeData->__toArray());
    }
}
