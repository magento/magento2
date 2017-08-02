<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Serializer used to update nested serialized data in product_options field.
 * @since 2.2.0
 */
class SerializedDataConverter extends SerializedToJson
{
    /**
     * Convert from serialized to JSON format.
     *
     * @param string $value
     * @return string
     *
     * @throws DataConversionException
     * @since 2.2.0
     */
    public function convert($value)
    {
        if ($this->isValidJsonValue($value)) {
            return $value;
        }
        $valueUnserialized = $this->unserializeValue($value);
        if (isset($valueUnserialized['options'])) {
            foreach ($valueUnserialized['options'] as $key => $option) {
                if ($option['option_type'] === 'file') {
                    $valueUnserialized['options'][$key]['option_value'] = parent::convert($option['option_value']);
                }
            }
        }
        if (isset($valueUnserialized['bundle_selection_attributes'])) {
            $valueUnserialized['bundle_selection_attributes'] = parent::convert(
                $valueUnserialized['bundle_selection_attributes']
            );
        }
        return $this->encodeJson($valueUnserialized);
    }
}
