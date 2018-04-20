<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class contains swatch attribute helper methods.
 */
class SwatchAttributeType
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * Data key which should populated to Attribute entity from "additional_data" field
     *
     * @var array
     */
    private $eavAttributeAdditionalDataKeys = [
        Swatch::SWATCH_INPUT_TYPE_KEY,
        'update_product_preview_image',
        'use_product_image_for_swatch',
    ];

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Checks if attribute is Textual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isTextSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_TEXT;
    }

    /**
     * Checks if attribute is Visual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isVisualSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }

    /**
     * Checks if an attribute is Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isSwatchAttribute(AttributeInterface $productAttribute): bool
    {
        return $this->isTextSwatch($productAttribute) || $this->isVisualSwatch($productAttribute);
    }

    /**
     * Unserializes attribute additional data and sets it to attribute object.
     *
     * @param AttributeInterface $attribute
     * @return void
     */
    private function populateAdditionalDataEavAttribute(AttributeInterface $attribute): void
    {
        if (!$attribute->hasData(Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $serializedAdditionalData = $attribute->getData('additional_data');
            if ($serializedAdditionalData) {
                $additionalData = $this->serializer->unserialize($serializedAdditionalData);
                if ($additionalData !== null && is_array($additionalData)) {
                    foreach ($this->eavAttributeAdditionalDataKeys as $key) {
                        if (isset($additionalData[$key])) {
                            $attribute->setData($key, $additionalData[$key]);
                        }
                    }
                }
            }
        }
    }
}
