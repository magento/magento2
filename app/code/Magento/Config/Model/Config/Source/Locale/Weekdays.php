<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locale timezone source
 */
namespace Magento\Config\Model\Config\Source\Locale;

class Weekdays implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->_localeLists = $localeLists;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_localeLists->getOptionWeekdays();
    }
}
