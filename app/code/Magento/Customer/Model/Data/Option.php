<?php
/**
 * Eav attribute option
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

/**
 * Class Option
 * @since 2.0.0
 */
class Option extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Customer\Api\Data\OptionInterface
{
    /**
     * Get option label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * Get option value
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Get nested options
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]|null
     * @since 2.0.0
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Set nested options
     *
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions(array $options = null)
    {
        return $this->setData(self::OPTIONS, $options);
    }
}
