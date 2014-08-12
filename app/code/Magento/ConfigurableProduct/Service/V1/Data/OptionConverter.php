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
namespace Magento\ConfigurableProduct\Service\V1\Data;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\ConfigurableProduct\Service\V1\Data\Option\ValueBuilder;
use Magento\ConfigurableProduct\Service\V1\Data\Option;
use Magento\ConfigurableProduct\Service\V1\Data\Option\ValueConverter;

class OptionConverter
{
    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\OptionBuilder
     */
    protected $optionBuilder;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\Option\ValueBuilder
     */
    protected $valueBuilder;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\Option\ValueConverter
     */
    private $valueConverter;

    /**
     * @param OptionBuilder $optionBuilder
     * @param ValueBuilder $valueBuilder
     * @param AttributeFactory $attributeFactory
     * @param ValueConverter $valueConverter
     */
    public function __construct(
        OptionBuilder $optionBuilder,
        ValueBuilder $valueBuilder,
        AttributeFactory $attributeFactory,
        ValueConverter $valueConverter
    ) {
        $this->optionBuilder = $optionBuilder;
        $this->valueBuilder = $valueBuilder;
        $this->attributeFactory = $attributeFactory;
        $this->valueConverter = $valueConverter;
    }

    /**
     * Convert configurable attribute to option data object
     *
     * @param Attribute $configurableAttribute
     * @return \Magento\ConfigurableProduct\Service\V1\Data\Option
     */
    public function convertFromModel(Attribute $configurableAttribute)
    {
        $values = [];
        $prices = $configurableAttribute->getPrices();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $values[] = $this->valueBuilder
                    ->setIndex($price['value_index'])
                    ->setPrice($price['pricing_value'])
                    ->setPercent($price['is_percent'])
                    ->create();
            }
        }

        $data = [
            Option::ID => $configurableAttribute->getId(),
            Option::ATTRIBUTE_ID => $configurableAttribute->getAttributeId(),
            Option::LABEL => $configurableAttribute->getLabel(),
            Option::TYPE => $configurableAttribute->getProductAttribute()->getFrontend()->getInputType(),
            Option::POSITION => $configurableAttribute->getPosition(),
            Option::USE_DEFAULT => $configurableAttribute->getData('use_default'),
            Option::VALUES => $values
        ];

        return $this->optionBuilder->populateWithArray($data)->create();
    }

    /**
     * @param Option $option
     * @return array
     */
    public function convertArrayFromData(Option $option)
    {
        $values = [];
        if (is_array($option->getValues())) {
            foreach ($option->getValues() as $value) {
                $values[] = $this->valueConverter->convertArrayFromData($value);
            }
        }
        return [
            'attribute_id' => $option->getAttributeId(),
            'position' => $option->getPosition(),
            'use_default' => $option->isUseDefault(),
            'label' => $option->getLabel(),
            'values' => $values
        ];
    }

    /**
     * @param Option $option
     * @param Attribute $configurableAttribute
     * @return Attribute
     */
    public function getModelFromData(Option $option, Attribute $configurableAttribute)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $returnConfigurableAttribute */
        $returnConfigurableAttribute = $this->attributeFactory->create();
        $returnConfigurableAttribute->setData($configurableAttribute->getData());
        $returnConfigurableAttribute->addData($option->__toArray());
        $returnConfigurableAttribute->setId($configurableAttribute->getId());
        $returnConfigurableAttribute->setAttributeId($configurableAttribute->getAttributeId());
        $returnConfigurableAttribute->setValues($configurableAttribute->getPrices());

        $values = $option->getValues();
        if (!is_null($values)) {
            $prices = [];
            foreach ($values as $value) {
                $prices[] = $this->valueConverter->convertArrayFromData($value);
            }
            $returnConfigurableAttribute->setValues($prices);
        }

        return $returnConfigurableAttribute;
    }
}
