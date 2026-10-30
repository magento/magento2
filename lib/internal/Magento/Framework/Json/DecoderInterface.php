<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Json;

/**
 * JSON decoder
 *
 * @api
 *
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::unserialize
 * @since 100.0.2
 */
interface DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format into a PHP type (array, string literal, etc.)
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data);
}
