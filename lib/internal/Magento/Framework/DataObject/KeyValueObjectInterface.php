<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject;

/**
 * Interface \Magento\Framework\DataObject\KeyValueObjectInterface
 *
 * @since 2.0.0
 */
interface KeyValueObjectInterface
{
    const KEY = 'key';
    const VALUE = 'value';

    /**
     * Get object key
     *
     * @return string
     * @since 2.0.0
     */
    public function getKey();

    /**
     * Set object key
     *
     * @param string $key
     * @return $this
     * @since 2.0.0
     */
    public function setKey($key);

    /**
     * Get object value
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set object value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);
}
