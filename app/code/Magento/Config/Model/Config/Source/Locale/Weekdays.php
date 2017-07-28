<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locale timezone source
 */
namespace Magento\Config\Model\Config\Source\Locale;

/**
 * @api
 * @since 2.0.0
 */
class Weekdays implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     * @since 2.0.0
     */
    protected $_localeLists;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->_localeLists = $localeLists;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_localeLists->getOptionWeekdays();
    }
}
