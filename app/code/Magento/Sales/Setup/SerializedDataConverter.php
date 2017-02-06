<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Serializer used to update nested serialized data in product_options field.
 */
class SerializedDataConverter implements \Magento\Framework\DB\DataConverter\DataConverterInterface
{
    /**
     * @var Serialize
     */
    private $serialize;

    /**
     * @var Json
     */
    private $json;

    /**
     * SerializedDataConverter constructor.
     *
     * @param Serialize $serialize
     * @param Json $json
     */
    public function __construct(
        Serialize $serialize,
        Json $json
    ) {
        $this->serialize = $serialize;
        $this->json = $json;
    }

    /**
     * Convert from serialized to JSON format.
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
