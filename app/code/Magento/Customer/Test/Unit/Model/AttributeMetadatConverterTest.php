<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Data\Option;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\DataObjectHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeMetadatConverterTest extends TestCase
{
    /**
     * @var OptionInterfaceFactory|MockObject
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory|MockObject
     */
    private $validationRuleFactory;

    /**
     * @var AttributeMetadataInterfaceFactory|MockObject
     */
    private $attributeMetadataFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /** @var  AttributeMetadataConverter */
    private $model;

    /** @var  Attribute|MockObject */
    private $attribute;

    protected function setUp(): void
    {
        $this->optionFactory = $this->getMockBuilder(OptionInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationRuleFactory = $this->getMockBuilder(ValidationRuleInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataFactory = $this->getMockBuilder(AttributeMetadataInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper =  $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeMetadataConverter(
            $this->optionFactory,
            $this->validationRuleFactory,
            $this->attributeMetadataFactory,
            $this->dataObjectHelper
        );
    }

    /**
     * @return array
     */
    private function prepareValidateRules()
    {
        return [
            'one' => 'numeric',
            'two' => 'alphanumeric'
        ];
    }

    /**
     * @return array
     */
    private function prepareOptions()
    {
        return [
            [
                'label' => 'few_values',
                'value' => [
                    [1], [2]
                ]
            ],
            [
                'label' => 'one_value',
                'value' => 1
            ]
        ];
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAttributeMetadataTestWithSource()
    {
        $validatedRules = $this->prepareValidateRules();
        $options = $this->prepareOptions();
        $optionDataObjectForSimpleValue1 = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionDataObjectForSimpleValue2 = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionObject1 = $this->getMockForAbstractClass(OptionInterface::class);
        $optionObject2 = $this->getMockForAbstractClass(OptionInterface::class);
        $this->optionFactory->expects($this->exactly(4))
            ->method('create')
            ->will(
                $this->onConsecutiveCalls(
                    $optionDataObjectForSimpleValue2,
                    $optionObject1,
                    $optionObject2,
                    $optionDataObjectForSimpleValue1
                )
            );
        $source = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn($options);
        $this->attribute->expects($this->once())
            ->method('usesSource')
            ->willReturn(true);
        $this->attribute->expects($this->once())
            ->method('getSource')
            ->willReturn($source);
        $optionDataObjectForSimpleValue1->expects($this->once())
            ->method('setValue')
            ->with(1);
        $optionDataObjectForSimpleValue2->expects($this->once())
            ->method('setLabel')
            ->with('few_values');
        $optionDataObjectForSimpleValue1->expects($this->once())
            ->method('setLabel')
            ->with('one_value');
        $this->dataObjectHelper->expects($this->exactly(2))
            ->method('populateWithArray')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($optionObject1, $optionObject2) {
                    if ($arg1 === $optionObject1 && $arg2 === ['1'] && $arg3 === OptionInterface::class) {
                        return null;
                    } elseif ($arg1 === $optionObject2 && $arg2 === ['2'] && $arg3 === OptionInterface::class) {
                        return null;
                    }
                }
            );
        $validationRule1 = $this->getMockForAbstractClass(ValidationRuleInterface::class);
        $validationRule2 = $this->getMockForAbstractClass(ValidationRuleInterface::class);
        $this->validationRuleFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($validationRule1, $validationRule2));
        $validationRule1->expects($this->once())
            ->method('setValue')
            ->with('numeric');
        $validationRule1->expects($this->once())
            ->method('setName')
            ->with('one')
            ->willReturnSelf();
        $validationRule2->expects($this->once())
            ->method('setValue')
            ->with('alphanumeric');
        $validationRule2->expects($this->once())
            ->method('setName')
            ->with('two')
            ->willReturnSelf();

        $mockMethods = ['setAttributeCode', 'setFrontendInput'];
        $attributeMetaData = $this->getMockBuilder(AttributeMetadata::class)
            ->onlyMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();
        foreach ($mockMethods as $method) {
            $attributeMetaData->expects($this->once())->method($method)->willReturnSelf();
        }

        $this->attribute->expects($this->once())
            ->method('getValidateRules')
            ->willReturn($validatedRules);
        $this->attributeMetadataFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeMetaData);
        $frontend = $this->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontend);
        $optionDataObjectForSimpleValue2->expects($this->once())
            ->method('setOptions')
            ->with([$optionObject1, $optionObject2]);
        $this->model->createMetadataAttribute($this->attribute);
    }
}
