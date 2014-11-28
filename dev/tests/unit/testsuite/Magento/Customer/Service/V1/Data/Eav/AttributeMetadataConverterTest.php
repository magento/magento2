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
namespace Magento\Customer\Service\V1\Data\Eav;

class AttributeMetadataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateMetadataAttribute()
    {
        $attribute = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->setMethods([
                'usesSource',
                'getSource',
                'getValidateRules',
                'getAttributeCode',
                'getFrontendInput',
                'getInputFilter',
                'getStoreLabel',
                'getIsVisible',
                'getIsRequired',
                'getMultilineCount',
                'getDataModel',
                'getFrontend',
                'getFrontendLabel',
                'getBackendType',
                'getNote',
                'getIsSystem',
                'getIsUserDefined',
                'getSortOrder',
                '__wakeup',
            ])
            ->getMock();

        $frontend = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend')
            ->disableOriginalConstructor()
            ->setMethods(['getClass'])
            ->getMock();
        $attribute->expects($this->once())->method('getFrontend')->will($this->returnValue($frontend));
        $frontendClass = 'class';
        $frontend->expects($this->once())->method('getClass')->will($this->returnValue($frontendClass));

        $attribute->expects($this->once())->method('usesSource')->will($this->returnValue(true));
        $source = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Source\AbstractSource')
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMock();
        $options = [
            [
                'label' => 'label1',
                'value' => 'value1',
            ],
            [
                'label' => 'label2',
                'value' => 'value2',
            ]
        ];
        $source->expects($this->once())->method('getAllOptions')->will($this->returnValue($options));
        $attribute->expects($this->once())->method('getSource')->will($this->returnValue($source));
        $attribute->expects($this->once())->method('getValidateRules')->will($this->returnValue(
            [
                'name1' => 'value1',
                'name2' => 'value2',
            ]
        ));

        $attributeCode = 'attrCode';
        $frontendInput = 'frontendInput';
        $inputFilter = 'inputFilter';
        $storeLabel = 'storeLabel';
        $isVisible = true;
        $isRequired = true;
        $multilineCount = 0;
        $dataModel = 'dataModel';
        $frontendLabel = 'frontendLabel';
        $backendType = 'backendType';
        $note = 'note';
        $isSystem = true;
        $isUserDefined = true;
        $sortOrder = 0;

        $attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $attribute->expects($this->once())->method('getFrontendInput')->will($this->returnValue($frontendInput));
        $attribute->expects($this->once())->method('getInputFilter')->will($this->returnValue($inputFilter));
        $attribute->expects($this->once())->method('getStoreLabel')->will($this->returnValue($storeLabel));
        $attribute->expects($this->once())->method('getIsVisible')->will($this->returnValue($isVisible));
        $attribute->expects($this->once())->method('getIsRequired')->will($this->returnValue($isRequired));
        $attribute->expects($this->once())->method('getMultilineCount')->will($this->returnValue($multilineCount));
        $attribute->expects($this->once())->method('getDataModel')->will($this->returnValue($dataModel));
        $attribute->expects($this->once())->method('getFrontendLabel')->will($this->returnValue($frontendLabel));
        $attribute->expects($this->once())->method('getBackendType')->will($this->returnValue($backendType));
        $attribute->expects($this->once())->method('getNote')->will($this->returnValue($note));
        $attribute->expects($this->once())->method('getIsSystem')->will($this->returnValue($isSystem));
        $attribute->expects($this->once())->method('getIsUserDefined')->will($this->returnValue($isUserDefined));
        $attribute->expects($this->once())->method('getSortOrder')->will($this->returnValue($sortOrder));

        $option1Mock = $this->getMockBuilder('Magento\Customer\Model\Data\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $option2Mock = $this->getMockBuilder('Magento\Customer\Model\Data\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $expectedOptions = [$option1Mock, $option2Mock];

        $rule1Mock = $this->getMockBuilder('Magento\Customer\Model\Data\ValidationRule')
            ->disableOriginalConstructor()
            ->getMock();
        $rule2Mock = $this->getMockBuilder('Magento\Customer\Model\Data\ValidationRule')
            ->disableOriginalConstructor()
            ->getMock();
        $expectedRules = [$rule1Mock, $rule2Mock];

        $expectedAttributeMock = $this->getMockBuilder('Magento\Customer\Model\Data\AttributeMetadata')
            ->disableOriginalConstructor()
            ->setMethods([
                    'usesSource',
                    'getSource',
                    'getValidateRules',
                    'getAttributeCode',
                    'getFrontendInput',
                    'getInputFilter',
                    'getStoreLabel',
                    'isVisible',
                    'isRequired',
                    'getMultilineCount',
                    'getDataModel',
                    'getFrontendClass',
                    'getFrontendLabel',
                    'getBackendType',
                    'getNote',
                    'isSystem',
                    'isUserDefined',
                    'getSortOrder',
                    'getOptions',
                    'getValidationRules',
                    '__wakeup',
                ])
            ->getMock();
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->will($this->returnValue($frontendInput));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getInputFilter')
            ->will($this->returnValue($inputFilter));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getStoreLabel')
            ->will($this->returnValue($storeLabel));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('isVisible')
            ->will($this->returnValue($isVisible));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('isRequired')
            ->will($this->returnValue($isRequired));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getMultilineCount')
            ->will($this->returnValue($multilineCount));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getDataModel')
            ->will($this->returnValue($dataModel));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getFrontendClass')
            ->will($this->returnValue($frontendClass));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getFrontendLabel')
            ->will($this->returnValue($frontendLabel));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getBackendType')
            ->will($this->returnValue($backendType));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getNote')
            ->will($this->returnValue($note));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('isSystem')
            ->will($this->returnValue($isSystem));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('isUserDefined')
            ->will($this->returnValue($isUserDefined));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getSortOrder')
            ->will($this->returnValue($sortOrder));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($expectedOptions));
        $expectedAttributeMock
            ->expects($this->once())
            ->method('getValidationRules')
            ->will($this->returnValue($expectedRules));

        /** @var \Magento\Customer\Api\Data\OptionDataBuilder $optionDataBuilderMock */
        $optionDataBuilderMock = $this->getMockBuilder('Magento\Customer\Api\Data\OptionDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setLabel', 'setValue', 'populateWithArray', 'setOptions', 'create'])
            ->getMock();
        $optionDataBuilderMock->expects($this->any())
            ->method('setValue')
            ->will($this->returnValue($optionDataBuilderMock));

        /** @var \Magento\Customer\Api\Data\ValidationRuleDataBuilder $validationRulesBuilderMock */
        $validationRulesBuilderMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setValue', 'create'])
            ->getMock();
        $validationRulesBuilderMock->expects($this->any())
            ->method('setName')
            ->withAnyParameters()
            ->will($this->returnSelf());
        $validationRulesBuilderMock->expects($this->any())
            ->method('setValue')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $attributeMetadataBuilderMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods([
                    'setAttributeCode',
                    'setFrontendInput',
                    'setInputFilter',
                    'setStoreLabel',
                    'setValidationRules',
                    'setVisible',
                    'setRequired',
                    'setMultilineCount',
                    'setDataModel',
                    'setOptions',
                    'setFrontendClass',
                    'setFrontendLabel',
                    'setNote',
                    'setSystem',
                    'setUserDefined',
                    'setBackendType',
                    'setSortOrder',
                    'create'
                ])
            ->getMock();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setAttributeCode')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setFrontendInput')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setInputFilter')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setStoreLabel')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setValidationRules')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setVisible')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setRequired')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setMultilineCount')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setDataModel')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setOptions')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setFrontendClass')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setFrontendLabel')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setNote')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setSystem')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setUserDefined')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setBackendType')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('setSortOrder')
            ->withAnyParameters()
            ->willReturnSelf();
        $attributeMetadataBuilderMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($expectedAttributeMock));

        $converter = $this->objectManager->getObject(
            'Magento\Customer\Model\AttributeMetadataConverter',
            [
                'optionBuilder' => $optionDataBuilderMock,
                'validationRuleBuilder' => $validationRulesBuilderMock,
                'attributeMetadataBuilder' => $attributeMetadataBuilderMock
            ]
        );

        /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface $metadataAttribute */
        $metadataAttribute = $converter->createMetadataAttribute($attribute);

        $this->assertEquals($attributeCode, $metadataAttribute->getAttributeCode());
        $this->assertEquals($frontendInput, $metadataAttribute->getFrontendInput());
        $this->assertEquals($inputFilter, $metadataAttribute->getInputFilter());
        $this->assertEquals($storeLabel, $metadataAttribute->getStoreLabel());
        $this->assertEquals($isVisible, $metadataAttribute->isVisible());
        $this->assertEquals($isRequired, $metadataAttribute->isRequired());
        $this->assertEquals($multilineCount, $metadataAttribute->getMultilineCount());
        $this->assertEquals($dataModel, $metadataAttribute->getDataModel());
        $this->assertEquals($frontendClass, $metadataAttribute->getFrontendClass());
        $this->assertEquals($frontendLabel, $metadataAttribute->getFrontendLabel());
        $this->assertEquals($backendType, $metadataAttribute->getBackendType());
        $this->assertEquals($note, $metadataAttribute->getNote());
        $this->assertEquals($isSystem, $metadataAttribute->isSystem());
        $this->assertEquals($isUserDefined, $metadataAttribute->isUserDefined());
        $this->assertEquals($sortOrder, $metadataAttribute->getSortOrder());

        $this->assertEquals($expectedOptions, $metadataAttribute->getOptions());
        $this->assertEquals($expectedRules, $metadataAttribute->getValidationRules());
    }
}
