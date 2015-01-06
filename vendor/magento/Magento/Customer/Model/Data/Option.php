<?php
/**
 * Eav attribute option
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Model\Data;

/**
 * Class Option
 */
class Option extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\OptionInterface
{
    /**
     * Get option label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * Get option value
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Get nested options
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]|null
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }
}
