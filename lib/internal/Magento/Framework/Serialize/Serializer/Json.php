<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class for serializing data to json string and unserializing json string to data
 */
class Json implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function serialize($data, $options = [])
    {

        $prettyPrint = (isset($options['prettyPrint']) && ($options['prettyPrint'] == true));

        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_NUMERIC_CHECK;

        if ($prettyPrint && defined('JSON_PRETTY_PRINT')) {
            $encodeOptions |= JSON_PRETTY_PRINT;
            $prettyPrint = false;
        }

        $encodedResult = json_encode($data, $encodeOptions);
        
        if ($prettyPrint) {
            $encodedResult = self::prettyPrint($encodedResult, array("intent" => "    "));
        }

        return $encodedResult;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        return json_decode($string, true);
    }
}
