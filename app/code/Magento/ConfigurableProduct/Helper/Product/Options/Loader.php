<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Helper\Product\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;

/**
 * Class Loader
 */
class Loader
{
    /**
     * @var OptionValueInterfaceFactory
     */
    private $optionValueFactory;

    /**
     * ReadHandler constructor
     *
     * @param OptionValueInterfaceFactory $optionValueFactory
     */
    public function __construct(OptionValueInterfaceFactory $optionValueFactory)
    {
        $this->optionValueFactory = $optionValueFactory;
    }

    /**
     * @param ProductInterface $product
     * @return OptionInterface[]
     */
    public function load(ProductInterface $product)
    {
        $options = [];
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $attributeCollection = $typeInstance->getConfigurableAttributes($product);

        foreach ($attributeCollection as $attribute) {
            $values = [];
            $attributeOptions = $attribute->getOptions();
            if (is_array($attributeOptions)) {
                foreach ($attributeOptions as $option) {
                    /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                    $value = $this->optionValueFactory->create();
                    $value->setValueIndex($option['value_index']);
                    $values[] = $value;
                }
            }
            $attribute->setValues($values);
            $options[] = $attribute;
        }

        return $options;
    }
}
