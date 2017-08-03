<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * JSON encoder
 *
 * @api
 *
 * @deprecated 2.2.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 */
interface EncoderInterface
{
    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @return string
     */
    public function encode($data);
}
