<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject;

/**
 * Interface \Magento\Framework\DataObject\KeyValueObjectInterface
 *
 * @api
 */
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
     * Set object key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key);

    /**
     * Get object value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set object value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
