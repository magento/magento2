<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

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
     * Key is productId, value is list of attributes
     * @var Attribute[]
     */
    private $attributesPerProduct;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param Configurable $typeConfigurable
     * @param SwatchAttributeCodes $swatchAttributeCodes
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Configurable $typeConfigurable,
        SwatchAttributeCodes $swatchAttributeCodes,
        SerializerInterface $serializer
    ) {
        $this->typeConfigurable = $typeConfigurable;
        $this->swatchAttributeCodes = $swatchAttributeCodes;
        $this->serializer = $serializer;
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
                if (array_key_exists($configurableAttribute->getAttributeId(), $swatchAttributeCodeMap)) {
                    $productAttribute = $configurableAttribute->getProductAttribute();

                    // Check if product attribute is actually an swatch attribute
                    if ($productAttribute && $this->isSwatchAttribute($productAttribute)) {
                        $swatchAttributes[$configurableAttribute->getAttributeId()] = $productAttribute;
                    }
                }
            }
            $this->attributesPerProduct[$product->getId()] = $swatchAttributes;
        }
        return $this->attributesPerProduct[$product->getId()];
    }

    /**
     * Get data key which should populated to attribute entity from "additional_data" field
     *
     * @return array
     */
    public function getEavAttributeAdditionalDataKeys()
    {
        return [
            \Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY,
            'update_product_preview_image',
            'use_product_image_for_swatch'
        ];
    }

    /**
     * Populate eav attribute with additional data from "additional_data" field
     *
     * @param AbstractAttribute $attribute
     * @return $this
     */
    public function populateAdditionalDataEavAttribute(AbstractAttribute $attribute)
    {
        $serializedAdditionalData = $attribute->getData('additional_data');

        if ($serializedAdditionalData) {
            $additionalData = $this->serializer->unserialize($serializedAdditionalData);

            if (isset($additionalData) && is_array($additionalData)) {
                foreach ($this->getEavAttributeAdditionalDataKeys() as $key) {
                    if (isset($additionalData[$key])) {
                        $attribute->setData($key, $additionalData[$key]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Check if an attribute is Swatch
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function isSwatchAttribute(AbstractAttribute $attribute)
    {
        $result = $this->isVisualSwatch($attribute) || $this->isTextSwatch($attribute);
        return $result;
    }

    /**
     * Is attribute Visual Swatch
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function isVisualSwatch(AbstractAttribute $attribute)
    {
        if (!$attribute->hasData(\Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $this->populateAdditionalDataEavAttribute($attribute);
        }
        return $attribute->getData(\Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY) == \Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }

    /**
     * Is attribute Textual Swatch
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function isTextSwatch(AbstractAttribute $attribute)
    {
        if (!$attribute->hasData(\Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $this->populateAdditionalDataEavAttribute($attribute);
        }
        return $attribute->getData(\Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY) == \Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_TEXT;
    }
}
