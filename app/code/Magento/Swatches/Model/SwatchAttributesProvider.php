<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Provide list of swatch attributes for product.
 */
class SwatchAttributesProvider
{
    /**
     * @var Configurable
     */
    private $typeConfigurable;

    /**
     * @var SwatchAttributeCodes
     */
    private $swatchAttributeCodes;

    /**
     * @var [productId => Attribute[]]
     */
    private $attributesPerProduct;

    /**
     * @param Configurable $typeConfigurable
     * @param SwatchAttributeCodes $swatchAttributeCodes
     */
    public function __construct(
        Configurable $typeConfigurable,
        SwatchAttributeCodes $swatchAttributeCodes
    ) {
        $this->typeConfigurable = $typeConfigurable;
        $this->swatchAttributeCodes = $swatchAttributeCodes;
    }

    /**
     * Provide list of swatch attributes for product. If product is not configurable return empty array.
     *
     * @param Product $product
     * @return Attribute[]
     */
    public function provide(Product $product)
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }
        if (!isset($this->attributesPerProduct[$product->getId()])) {
            $configurableAttributes = $this->typeConfigurable->getConfigurableAttributes($product);
            $swatchAttributeIds = array_keys($this->swatchAttributeCodes->getCodes());
            $swatchAttributes = [];
            foreach ($configurableAttributes as $configurableAttribute) {
                if (in_array($configurableAttribute->getAttributeId(), $swatchAttributeIds)) {
                    $swatchAttributes[$configurableAttribute->getAttributeId()]
                        = $configurableAttribute->getProductAttribute();
                }
            }
            $this->attributesPerProduct[$product->getId()] = $swatchAttributes;
        }
        return $this->attributesPerProduct[$product->getId()];
    }
}
