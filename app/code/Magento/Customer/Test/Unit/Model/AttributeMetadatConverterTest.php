<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use Magento\Framework\App\Config\ScopeConfigInterface;
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

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /** @var  AttributeMetadataConverter */
    private $model;

    /** @var  Attribute|MockObject */
    private $attribute;

    protected function setUp(): void
    {
        $this->optionFactory = $this->createPartialMock(
            OptionInterfaceFactory::class,
            ['create']
        );
        $this->validationRuleFactory = $this->createPartialMock(
            ValidationRuleInterfaceFactory::class,
            ['create']
        );
        $this->attributeMetadataFactory = $this->createPartialMock(
            AttributeMetadataInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelper =  $this->createMock(DataObjectHelper::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->attribute = $this->createMock(Attribute::class);

        $this->model = new AttributeMetadataConverter(
            $this->optionFactory,
            $this->validationRuleFactory,
            $this->attributeMetadataFactory,
            $this->dataObjectHelper,
            $this->scopeConfig
        );
    }

    /**
     * @return array<string, string>
     */
    private function prepareValidateRules()
    {
        return [
            'one' => 'numeric',
            'two' => 'alphanumeric'
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
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
     * @SuppressWarnings(PHPMD)
     */
    public function testCreateAttributeMetadataTestWithSource()
    {
        $validatedRules = $this->prepareValidateRules();
        $options = $this->prepareOptions();
        $optionDataObjectForSimpleValue1 = $this->createMock(Option::class);
        $optionDataObjectForSimpleValue2 = $this->createMock(Option::class);
        $optionObject1 = $this->createMock(OptionInterface::class);
        $optionObject2 = $this->createMock(OptionInterface::class);
        $this->optionFactory->expects($this->exactly(4))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $optionDataObjectForSimpleValue2,
                $optionObject1,
                $optionObject2,
                $optionDataObjectForSimpleValue1
            );
        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn($options);
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('usesSource')
            ->willReturn(true);
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
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
        $this->dataObjectHelper->expects($this->exactly(2)) // @phpstan-ignore method.notFound
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
        $validationRule1 = $this->createMock(ValidationRuleInterface::class);
        $validationRule2 = $this->createMock(ValidationRuleInterface::class);
        $this->validationRuleFactory->expects($this->exactly(2)) // @phpstan-ignore method.notFound
            ->method('create')
            ->willReturnOnConsecutiveCalls($validationRule1, $validationRule2);
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
        $attributeMetaData = $this->createPartialMock(
            AttributeMetadata::class,
            $mockMethods
        );
        foreach ($mockMethods as $method) {
            $attributeMetaData->expects($this->once())->method($method)->willReturnSelf();
        }

        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('getValidateRules')
            ->willReturn($validatedRules);
        $this->attributeMetadataFactory->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('create')
            ->willReturn($attributeMetaData);
        $frontend = $this->createMock(AbstractFrontend::class);
        $this->attribute->expects($this->once()) // @phpstan-ignore method.notFound
            ->method('getFrontend')
            ->willReturn($frontend);
        $optionDataObjectForSimpleValue2->expects($this->once())
            ->method('setOptions')
            ->with([$optionObject1, $optionObject2]);
        // @phpstan-ignore argument.type
        $this->model->createMetadataAttribute($this->attribute);
    }
}
