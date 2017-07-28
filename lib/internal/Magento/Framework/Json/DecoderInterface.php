<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * JSON decoder
 *
 * @api
 *
 * @deprecated 2.2.0 @see \Magento\Framework\Serialize\Serializer\Json::unserialize
 * @since 2.0.0
 */
interface DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format into a PHP type (array, string literal, etc.)
     *
     * @param string $data
     * @return mixed
     * @since 2.0.0
     */
    public function decode($data);
}
