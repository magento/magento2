<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Helper;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Swatches\Model\Swatch;

/**
 * Provide list of swatch attributes for product.
 */
class Attribute
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Get data key which should populated to attribute entity from "additional_data" field
     *
     * @return array
     */
    public function getEavAttributeAdditionalDataKeys()
    {
        return [
            Swatch::SWATCH_INPUT_TYPE_KEY,
            'update_product_preview_image',
            'use_product_image_for_swatch'
        ];
    }

    /**
     * @param AbstractAttribute $attribute
     * @return $this
     */
    public function assembleAdditionalDataEavAttribute(AbstractAttribute $attribute)
    {
        $initialAdditionalData = [];
        $additionalData = (string) $attribute->getData('additional_data');
        if (!empty($additionalData)) {
            $additionalData = $this->serializer->unserialize($additionalData);
            if (is_array($additionalData)) {
                $initialAdditionalData = $additionalData;
            }
        }

        $dataToAdd = [];
        foreach ($this->getEavAttributeAdditionalDataKeys() as $key) {
            $dataValue = $attribute->getData($key);
            if (null !== $dataValue) {
                $dataToAdd[$key] = $dataValue;
            }
        }
        $additionalData = array_merge($initialAdditionalData, $dataToAdd);
        $attribute->setData('additional_data', $this->serializer->serialize($additionalData));
        return $this;
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
        if (!$attribute->hasData(Swatch::SWATCH_INPUT_TYPE_KEY)) {
            $this->populateAdditionalDataEavAttribute($attribute);
        }
        return $attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_VISUAL;
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
        return $attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_TEXT;
    }
}
