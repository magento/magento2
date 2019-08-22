<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Serializer used to additionally handle the data conversion of Vault token metadata
 */
class SalesOrderPaymentDataConverter extends SerializedToJson
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

        $unserializedValue = $this->unserializeValue($value);
        if (isset($unserializedValue['token_metadata'])) {
            $unserializedValue['customer_id'] = $unserializedValue['token_metadata']['customer_id'];
            $unserializedValue['public_hash'] = $unserializedValue['token_metadata']['public_hash'];
            unset($unserializedValue['token_metadata']);
        }

        return $this->encodeJson($unserializedValue);
    }
}
