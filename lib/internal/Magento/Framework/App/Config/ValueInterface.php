<?php
/**
 * Value interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Interface \Magento\Framework\App\Config\ValueInterface
 *
 * @since 2.0.0
 */
interface ValueInterface
{
    /**
     * Table name
     */
    const ENTITY = 'config_data';

    /**
     * Check if config data value was changed
     * @todo this method should be make as protected
     * @return bool
     * @since 2.0.0
     */
    public function isValueChanged();

    /**
     * Get old value from existing config
     *
     * @return string
     * @since 2.0.0
     */
    public function getOldValue();

    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    public function getFieldsetDataValue($key);
}
