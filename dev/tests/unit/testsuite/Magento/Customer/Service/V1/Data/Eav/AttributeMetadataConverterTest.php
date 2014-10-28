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

        $converter = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadataConverter'
        );

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

        $optionBuilder = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\Data\Eav\OptionBuilder'
        );
        $expectedOptions = [
            $optionBuilder->setLabel('label1')->setValue('value1')->create(),
            $optionBuilder->setLabel('label2')->setValue('value2')->create(),
        ];
        $this->assertEquals($expectedOptions, $metadataAttribute->getOptions());

        $validateRulesBuilder = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder'
        );
        $expectedRules = [
            $validateRulesBuilder->setName('name1')->setValue('value1')->create(),
            $validateRulesBuilder->setName('name2')->setValue('value2')->create(),
        ];
        $this->assertEquals($expectedRules, $metadataAttribute->getValidationRules());
    }
}
