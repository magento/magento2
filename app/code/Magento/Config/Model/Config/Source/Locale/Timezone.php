<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locale timezone source
 */
namespace Magento\Config\Model\Config\Source\Locale;

class Timezone implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Timezones that works incorrect with php_intl extension
     */
    const IGNORED_TIMEZONES = [
        'Antarctica/Troll',
        'Asia/Chita',
        'Asia/Srednekolymsk',
        'Pacific/Bougainville'
    ];

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
        $timezones = $this->_localeLists->getOptionTimezones();
        $timezones = array_filter($timezones, function($value) {
            if (in_array($value['value'], static::IGNORED_TIMEZONES)) {
                return false;
            }
            return true;
        });

        return $timezones;
    }
}
