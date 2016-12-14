<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Item\Converter\ProductOptions;

/**
 * Class SerializedToJson
 * @package Magento\Sales\Model\Order\Item\Converter\ProductOptions
 *
 * Serializer used to update nested serialized data in product_options field
 */
class SerializedToJson extends \Magento\Framework\DB\DataConverter\SerializedToJson
{
    /**
     * Convert from serialized to JSON format
     *
     * @param string $value
     * @return string
     */
    public function convert($value)
    {
        $valueUnserialized = $this->serialize->unserialize($value);
        if (isset($valueUnserialized['options'])) {
            foreach ($valueUnserialized['options'] as $key => $option) {
                if ($option['option_type'] === 'file') {
                    $valueUnserialized['options'][$key]['option_value'] = $this->json->serialize(
                        $this->serialize->unserialize(
                            $option['option_value']
                        )
                    );
                }
            }
        }
        if (isset($valueUnserialized['bundle_selection_attributes'])) {
            $valueUnserialized['bundle_selection_attributes'] = $this->json->serialize(
                $this->serialize->unserialize(
                    $valueUnserialized['bundle_selection_attributes']
                )
            );
        }
        return $this->json->serialize($valueUnserialized);
    }
}
