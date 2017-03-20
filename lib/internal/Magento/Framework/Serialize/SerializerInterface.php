<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

/**
 * Interface for serializing
 */
interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @param array $options
     * @return string|bool
     */
    public function serialize($data, $options = []);

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    public function unserialize($string);
}
