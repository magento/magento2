<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * Json encoder
 *
 */
interface EncoderInterface
{
    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function encode($data, $cycleCheck = false, $options = []);
}
