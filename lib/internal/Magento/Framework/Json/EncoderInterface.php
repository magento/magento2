<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * JSON encoder
 *
 * @api
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
