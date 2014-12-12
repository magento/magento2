<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
