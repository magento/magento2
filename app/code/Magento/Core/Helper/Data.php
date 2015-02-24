<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

/**
 * Core data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = [])
    {
        $json = \Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        $this->translateInline->processResponseBody($json, true);
        return $json;
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     * @param int $objectDecodeType
     * @return mixed
     */
    public function jsonDecode($encodedValue, $objectDecodeType = \Zend_Json::TYPE_ARRAY)
    {
        return \Zend_Json::decode($encodedValue, $objectDecodeType);
    }
}
