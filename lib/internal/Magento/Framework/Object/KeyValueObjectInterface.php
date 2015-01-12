<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Object;

interface KeyValueObjectInterface
{
    const KEY = 'key';
    const VALUE = 'value';

    /**
     * Get object key
     *
     * @return string
     */
    public function getKey();

    /**
     * Get object value
     *
     * @return string
     */
    public function getValue();
}
