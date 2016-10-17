<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|integer|float|boolean|array|null $data
     * @return string|boolean
     */
    public function serialize($data);

    /**
     * Unserialize the given string into data
     *
     * @param string $string
     * @return string|integer|float|boolean|array|null
     */
    public function unserialize($string);
}
