<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Data\Option;
use Magento\Customer\Model\Data\ValidationRule;
use Magento\Customer\Model\Metadata\AttributeMetadataHydrator;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeMetadataHydratorTest extends TestCase
{
    /**
     * @var AttributeMetadataInterfaceFactory|MockObject
     */
    private $attributeMetadataFactoryMock;

    /**
     * @var OptionInterfaceFactory|MockObject
     */
    private $optionFactoryMock;

    /**
     * @var ValidationRuleInterfaceFactory|MockObject
     */
    private $validationRuleFactoryMock;

    /**
     * @var AttributeMetadataInterface|MockObject
     */
    private $attributeMetadataMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var AttributeMetadataHydrator|MockObject
     */
    private $attributeMetadataHydrator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->attributeMetadataFactoryMock = $this->createPartialMock(
            AttributeMetadataInterfaceFactory::class,
            ['create']
        );
        $this->optionFactoryMock = $this->createPartialMock(OptionInterfaceFactory::class, ['create']);
        $this->validationRuleFactoryMock = $this->createPartialMock(ValidationRuleInterfaceFactory::class, ['create']);
        $this->attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->attributeMetadataHydrator = $objectManager->getObject(
            AttributeMetadataHydrator::class,
            [
                'attributeMetadataFactory' => $this->attributeMetadataFactoryMock,
                'optionFactory' => $this->optionFactoryMock,
                'validationRuleFactory' => $this->validationRuleFactoryMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testHydrate(): void
    {
        $optionOneData = [
            'label' => 'Label 1',
            'options' => null
        ];
        $optionThreeData = [
            'label' => 'Label 3',
            'options' => null
        ];
        $optionFourData = [
            'label' => 'Label 4',
            'options' => null
        ];
        $optionTwoData = [
            'label' => 'Label 2',
            'options' => [$optionThreeData, $optionFourData]
        ];
        $validationRuleOneData = [
            'name' => 'Name 1',
            'value' => 'Value 1'
        ];
        $attributeMetadataData = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
            'options' => [$optionOneData, $optionTwoData],
            'validation_rules' => [$validationRuleOneData]
        ];

        $optionOne = new Option($optionOneData);
        $optionThree = new Option($optionThreeData);
        $optionFour = new Option($optionFourData);

        $optionTwoDataPartiallyConverted = [
            'label' => 'Label 2',
            'options' => [$optionThree, $optionFour]
        ];
        $optionFive = new Option($optionTwoDataPartiallyConverted);
        $this->optionFactoryMock
            ->method('create')
            ->withConsecutive(
                [['data' => $optionOneData]],
                [['data' => $optionThreeData]],
                [['data' => $optionFourData]],
                [['data' => $optionTwoDataPartiallyConverted]]
            )
            ->willReturnOnConsecutiveCalls($optionOne, $optionThree, $optionFour, $optionFive);

        $validationRuleOne = new ValidationRule($validationRuleOneData);
        $this->validationRuleFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $validationRuleOneData])
            ->willReturn($validationRuleOne);

        $attributeMetadataPartiallyConverted = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
            'options' => [$optionOne, $optionFive],
            'validation_rules' => [$validationRuleOne]
        ];

        $this->attributeMetadataFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $attributeMetadataPartiallyConverted])
            ->willReturn(
                new AttributeMetadata($attributeMetadataPartiallyConverted)
            );

        $attributeMetadata = $this->attributeMetadataHydrator->hydrate($attributeMetadataData);

        $this->assertInstanceOf(AttributeMetadataInterface::class, $attributeMetadata);
        $this->assertEquals(
            $attributeMetadataData['attribute_code'],
            $attributeMetadata->getAttributeCode()
        );
        $this->assertIsArray($attributeMetadata->getOptions());
        $this->assertArrayHasKey(
            0,
            $attributeMetadata->getOptions()
        );
        $this->assertInstanceOf(OptionInterface::class, $attributeMetadata->getOptions()[0]);
        $this->assertEquals(
            $optionOneData['label'],
            $attributeMetadata->getOptions()[0]->getLabel()
        );
        $this->assertArrayHasKey(1, $attributeMetadata->getOptions());
        $this->assertInstanceOf(OptionInterface::class, $attributeMetadata->getOptions()[1]);

        $this->assertIsArray($attributeMetadata->getOptions()[1]->getOptions());
        $this->assertArrayHasKey(0, $attributeMetadata->getOptions()[1]->getOptions());
        $this->assertInstanceOf(OptionInterface::class, $attributeMetadata->getOptions()[1]->getOptions()[0]);
        $this->assertEquals(
            $optionThreeData['label'],
            $attributeMetadata->getOptions()[1]->getOptions()[0]->getLabel()
        );
        $this->assertIsArray($attributeMetadata->getValidationRules());
        $this->assertArrayHasKey(0, $attributeMetadata->getValidationRules());
        $this->assertInstanceOf(ValidationRuleInterface::class, $attributeMetadata->getValidationRules()[0]);
        $this->assertEquals(
            $validationRuleOneData['name'],
            $attributeMetadata->getValidationRules()[0]->getName()
        );
    }

    /**
     * @return void
     */
    public function testExtract(): void
    {
        $data = ['foo' => 'bar'];
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with(
                $this->attributeMetadataMock,
                AttributeMetadata::class
            )
            ->willReturn($data);
        $this->assertSame(
            $data,
            $this->attributeMetadataHydrator->extract($this->attributeMetadataMock)
        );
    }
}
