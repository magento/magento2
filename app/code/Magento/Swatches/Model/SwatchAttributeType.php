<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Swatches\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;

<<<<<<< HEAD
class SwatchAttributeType
{

=======
/**
 * Class contains swatch attribute helper methods.
 */
class SwatchAttributeType
{
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
        'use_product_image_for_swatch'
    ];

    /**
     * SwatchAttributeType constructor.
=======
        'use_product_image_for_swatch',
    ];

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
<<<<<<< HEAD
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isTextSwatch(AttributeInterface $productAttribute)
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);
=======
     * Checks if attribute is Textual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isTextSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_TEXT;
    }

    /**
<<<<<<< HEAD
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isVisualSwatch(AttributeInterface $productAttribute)
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);
=======
     * Checks if attribute is Visual Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isVisualSwatch(AttributeInterface $productAttribute): bool
    {
        $this->populateAdditionalDataEavAttribute($productAttribute);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $productAttribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }

    /**
<<<<<<< HEAD
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isSwatchAttribute(AttributeInterface $productAttribute)
=======
     * Checks if an attribute is Swatch.
     *
     * @param AttributeInterface $productAttribute
     * @return bool
     */
    public function isSwatchAttribute(AttributeInterface $productAttribute): bool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->isTextSwatch($productAttribute) || $this->isVisualSwatch($productAttribute);
    }

    /**
<<<<<<< HEAD
     * @param AttributeInterface $attribute
     * @return void
     */
    private function populateAdditionalDataEavAttribute(AttributeInterface $attribute)
=======
     * Unserializes attribute additional data and sets it to attribute object.
     *
     * @param AttributeInterface $attribute
     * @return void
     */
    private function populateAdditionalDataEavAttribute(AttributeInterface $attribute): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
