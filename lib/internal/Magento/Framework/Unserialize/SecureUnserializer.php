<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Unserialize;

/**
 * Class provides functionality to unserialize data.
 *
 * @deprecated
 */
class SecureUnserializer
{
    /**
     * Unserialize data from string.
     *
     * @param string $string
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unserialize($string)
    {
        if (preg_match('/[oc]:[+\-]?\d+:"/i', $string)) {
            throw new \InvalidArgumentException('Data contains serialized object and cannot be unserialized');
        }

        try {
            return unserialize($string);
        } catch (\Exception $e) {
            return false;
        }
    }
}
