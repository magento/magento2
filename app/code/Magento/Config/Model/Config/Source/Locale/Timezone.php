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
class Timezone implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Timezones that works incorrect with php_intl extension
     * @since 2.0.0
     */
    protected $ignoredTimezones = [
        'Antarctica/Troll',
        'Asia/Chita',
        'Asia/Srednekolymsk',
        'Pacific/Bougainville'
    ];

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
        $timezones = $this->_localeLists->getOptionTimezones();
        $timezones = array_filter($timezones, function ($value) {
            if (in_array($value['value'], $this->ignoredTimezones)) {
                return false;
            }
            return true;
        });

        return $timezones;
    }
}
