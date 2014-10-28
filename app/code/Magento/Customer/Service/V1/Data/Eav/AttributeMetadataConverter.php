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

/**
 * Converter for AttributeMetadata
 */
class AttributeMetadataConverter
{
    /**
     * @var OptionBuilder
     */
    private $_optionBuilder;

    /**
     * @var ValidationRuleBuilder
     */
    private $_validationRuleBuilder;

    /**
     * @var AttributeMetadataBuilder
     */
    private $_attributeMetadataBuilder;

    /**
     * Initialize the Converter
     *
     * @param OptionBuilder $optionBuilder
     * @param ValidationRuleBuilder $validationRuleBuilder
     * @param AttributeMetadataBuilder $attributeMetadataBuilder
     */
    public function __construct(
        OptionBuilder $optionBuilder,
        ValidationRuleBuilder $validationRuleBuilder,
        AttributeMetadataBuilder $attributeMetadataBuilder
    ) {
        $this->_optionBuilder = $optionBuilder;
        $this->_validationRuleBuilder = $validationRuleBuilder;
        $this->_attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * Create AttributeMetadata Data object from the Attribute Model
     *
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return AttributeMetadata
     */
    public function createMetadataAttribute($attribute)
    {
        $options = [];
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                if (!is_array($option['value'])) {
                    $this->_optionBuilder->setValue($option['value']);
                } else {
                    $optionArray = [];
                    foreach ($option['value'] as $optionArrayValues) {
                        $optionArray[] = $this->_optionBuilder->populateWithArray($optionArrayValues)->create();
                    }
                    $this->_optionBuilder->setOptions($optionArray);
                }
                $this->_optionBuilder->setLabel($option['label']);
                $options[] = $this->_optionBuilder->create();
            }
        }
        $validationRules = [];
        foreach ($attribute->getValidateRules() as $name => $value) {
            $validationRules[] = $this->_validationRuleBuilder->setName($name)
                ->setValue($value)
                ->create();
        }

        $this->_attributeMetadataBuilder->setAttributeCode($attribute->getAttributeCode())
            ->setFrontendInput($attribute->getFrontendInput())
            ->setInputFilter((string)$attribute->getInputFilter())
            ->setStoreLabel($attribute->getStoreLabel())
            ->setValidationRules($validationRules)
            ->setVisible((boolean)$attribute->getIsVisible())
            ->setRequired((boolean)$attribute->getIsRequired())
            ->setMultilineCount((int)$attribute->getMultilineCount())
            ->setDataModel((string)$attribute->getDataModel())
            ->setOptions($options)
            ->setFrontendClass($attribute->getFrontend()->getClass())
            ->setFrontendLabel($attribute->getFrontendLabel())
            ->setNote((string)$attribute->getNote())
            ->setIsSystem((boolean)$attribute->getIsSystem())
            ->setIsUserDefined((boolean)$attribute->getIsUserDefined())
            ->setBackendType($attribute->getBackendType())
            ->setSortOrder((int)$attribute->getSortOrder());

        return $this->_attributeMetadataBuilder->create();
    }
}
