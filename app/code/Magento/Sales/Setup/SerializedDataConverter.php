<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Serializer used to update nested serialized data in product_options and additional_information fields.
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
        if (isset($valueUnserialized['token_metadata'])) {
            $valueUnserialized[PaymentTokenInterface::CUSTOMER_ID] =
                $valueUnserialized['token_metadata'][PaymentTokenInterface::CUSTOMER_ID];
            $valueUnserialized[PaymentTokenInterface::PUBLIC_HASH] =
                $valueUnserialized['token_metadata'][PaymentTokenInterface::PUBLIC_HASH];
            unset($valueUnserialized['token_metadata']);
        }

        return $this->encodeJson($valueUnserialized);
    }
}
