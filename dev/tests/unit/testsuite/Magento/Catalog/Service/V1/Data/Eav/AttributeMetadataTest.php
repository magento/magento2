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

class AttributeMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder|\PHPUnit_Framework_TestCase */
    protected $builderMock;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\ValidationRule[] */
    protected $validationRules;

    /** @var \Magento\Catalog\Service\V1\Data\Eav\Option[] */
    protected $optionRules;

    protected function setUp()
    {
        $this->builderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(array('getData'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->validationRules = array(
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\ValidationRule', [], [], '', false),
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\ValidationRule', [], [], '', false)
        );

        $this->optionRules = array(
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\Option', [], [], '', false),
            $this->getMock('Magento\Catalog\Service\V1\Data\Eav\Option', [], [], '', false)
        );
    }

    /**
     * Test constructor and getters
     *
     * @dataProvider constructorAndGettersDataProvider
     */
    public function testConstructorAndGetters($method, $key, $expectedValue)
    {
        $this->builderMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([$key => $expectedValue]));
        $attributeMetadata = new AttributeMetadata($this->builderMock);
        $this->assertEquals($expectedValue, $attributeMetadata->$method());
    }

    public function constructorAndGettersDataProvider()
    {
        return array(
            ['getAttributeCode', AttributeMetadata::ATTRIBUTE_CODE, 'code'],
            ['getFrontendInput', AttributeMetadata::FRONTEND_INPUT, '<br>'],
            ['getValidationRules', AttributeMetadata::VALIDATION_RULES, $this->validationRules],
            ['isVisible', AttributeMetadata::VISIBLE, true],
            ['isRequired', AttributeMetadata::REQUIRED, true],
            ['getOptions', AttributeMetadata::OPTIONS, $this->optionRules],
            ['isUserDefined', AttributeMetadata::USER_DEFINED, false],
            ['getFrontendLabel', AttributeMetadata::FRONTEND_LABEL, 'Label'],
            ['getNote', AttributeMetadata::NOTE, 'Text Note'],
            ['getBackendType', AttributeMetadata::BACKEND_TYPE, 'Type']
        );
    }

    /**
     * Test applyTy method of builder
     *
     * ApplyTo method transform string to array
     *
     * @dataProvider applyToDataProvider()
     *
     * @param $applyTo
     */
    public function testApplyTo($applyTo)
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Catalog\Service\V1\Data\Eav\OptionBuilder $optionBuilder */
        $optionBuilder = $objectManager->getObject('Magento\Catalog\Service\V1\Data\Eav\OptionBuilder');
        /** @var \Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder $validationRuleBuilder */
        $validationRuleBuilder = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder'
        );
        $frontendLabelBuilder = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder'
        );

        $attributeBuilder = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder',
            [
                'optionBuilder' => $optionBuilder,
                'validationRuleBuilder' => $validationRuleBuilder,
                'frontendLabelBuilder' => $frontendLabelBuilder
            ]
        );
        $attributeBuilder->populateWithArray([AttributeMetadata::APPLY_TO => $applyTo]);

        $attributeMetadata = new AttributeMetadata($attributeBuilder);
        $this->assertTrue(is_array($attributeMetadata->getApplyTo()));
        $this->assertEquals(3, count($attributeMetadata->getApplyTo()));

        $attributeBuilder->setApplyTo($applyTo);
        $attributeMetadata = new AttributeMetadata($attributeBuilder);
        $this->assertTrue(is_array($attributeMetadata->getApplyTo()));
        $this->assertEquals(3, count($attributeMetadata->getApplyTo()));
    }

    public function applyToDataProvider()
    {
        return array(array(
            'simple,virtual,bundle',
            array('simple', 'virtual', 'bundle')
        ));
    }
}
