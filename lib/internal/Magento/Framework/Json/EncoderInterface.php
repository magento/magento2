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
 * @since 2.0.0
 */
interface EncoderInterface
{
    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @return string
     * @since 2.0.0
     */
    public function encode($data);
}
