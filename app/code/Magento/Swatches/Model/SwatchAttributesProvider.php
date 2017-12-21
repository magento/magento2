<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use \Magento\Swatches\Helper\Data as SwatchesHelper;
use Magento\Framework\App\ObjectManager;

/**
 * Provide list of swatch attributes for product.
 */
class SwatchAttributesProvider
{
    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchesHelper;
    /**
     * @var Configurable
     */
    private $typeConfigurable;

    /**
     * @var SwatchAttributeCodes
     */
    private $swatchAttributeCodes;

    /**
     * Key is productId, value is list of attributes
     * @var Attribute[]
     */
    private $attributesPerProduct;

    /**
     * SwatchAttributesProvider constructor.
     *
     * @param Configurable         $typeConfigurable
     * @param SwatchAttributeCodes $swatchAttributeCodes
     * @param SwatchesHelper|null    $swatchHelper
     */
    public function __construct(
        Configurable $typeConfigurable,
        SwatchAttributeCodes $swatchAttributeCodes,
        SwatchesHelper $swatchHelper = null
    ) {
        $this->typeConfigurable = $typeConfigurable;
        $this->swatchAttributeCodes = $swatchAttributeCodes;
        $this->swatchesHelper = $swatchHelper ?: ObjectManager::getInstance()->create(SwatchesHelper::class);
    }

    /**
     * Provide list of swatch attributes for product. If product is not configurable return empty array
     * Key is productId, value is list of attributes
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
            $swatchAttributeCodeMap = $this->swatchAttributeCodes->getCodes();

            $swatchAttributes = [];
            foreach ($configurableAttributes as $configurableAttribute) {
                if ($this->getIsSwatchAttribute($configurableAttribute->getProductAttribute())) {
                    if (array_key_exists($configurableAttribute->getAttributeId(), $swatchAttributeCodeMap)) {
                        $swatchAttributes[$configurableAttribute->getAttributeId()]
                            = $configurableAttribute->getProductAttribute();
                    }
                }
            }
            $this->attributesPerProduct[$product->getId()] = $swatchAttributes;
        }
        return $this->attributesPerProduct[$product->getId()];
    }

    /**
     * This method introduced only for the case when customer already has converted attribute.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttribute
     * @return bool
     * @deprecated
     */
    private function getIsSwatchAttribute($productAttribute)
    {
        return $this->swatchesHelper->isSwatchAttribute($productAttribute);
    }
}
