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
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 * @since 100.0.2
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
