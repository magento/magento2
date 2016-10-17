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
     * @param array|string $data
     * @return string|bool
     */
    public function serialize($data);

    /**
     * Unserialize the given string into array
     *
     * @param string $string
     * @param int $objectDecodeType
     * @return array
     */
    public function unserialize($string);
}
