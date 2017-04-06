<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * JSON encoder
 *
 * @api
 *
 * @deprecated @see \Magento\Framework\Serialize\Serializer\Json::serialize
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
