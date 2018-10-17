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
 * This interface cannot be marked as API since doesn't fit developers' needs of extensibility. In 2.4 we are going
 * to introduce a new interface which should cover all needs and deprecate the this one with the model
 * {@see \Magento\Framework\App\Config\Value}
 */
interface ValueInterface
{
    /**
     * Table name
     *
     * @deprecated since it is not used
     */
    const ENTITY = 'config_data';

    /**
     * Check if config data value was changed
     *
     * @todo this method should be make as protected
     * @return bool
     */
    public function isValueChanged();

    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue();

    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     */
    public function getFieldsetDataValue($key);
}
