<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Swatches\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;

<<<<<<< HEAD
/**
 * Class contains swatch attribute helper methods.
 */
class SwatchAttributeType
{
=======
class SwatchAttributeType
{

>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
        'use_product_image_for_swatch',
    ];

    /**
=======
        'use_product_image_for_swatch'
    ];

    /**
     * SwatchAttributeType constructor.
>>>>>>> upstream/2.2-develop
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
<<<<<<< HEAD
     * Checks if attribute is Textual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isTextSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

=======
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isTextSwatch(AttributeInterface $productAttribute)
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);
>>>>>>> upstream/2.2-develop
        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_TEXT;
    }

    /**
<<<<<<< HEAD
     * Checks if attribute is Visual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isVisualSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

=======
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isVisualSwatch(AttributeInterface $productAttribute)
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);
>>>>>>> upstream/2.2-develop
        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }

    /**
<<<<<<< HEAD
     * Checks if an attribute is Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isSwatchAttribute(AttributeInterface $productAttribute): bool
=======
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isSwatchAttribute(AttributeInterface $productAttribute)
>>>>>>> upstream/2.2-develop
    {
        return $this->isTextSwatch($productAttribute) || $this->isVisualSwatch($productAttribute);
    }

    /**
<<<<<<< HEAD
     * Unserializes attribute additional data and sets it to attribute object.
     *
     * @param AttributeInterface $attribute
     * @return void
     */
    private function populateAdditionalDataEavAttribute(AttributeInterface $attribute): void
=======
     * @param AttributeInterface $attribute
     * @return void
     */
    private function populateAdditionalDataEavAttribute(AttributeInterface $attribute)
>>>>>>> upstream/2.2-develop
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
