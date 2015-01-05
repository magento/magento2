<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Locale weekdays source
 */
namespace Magento\Backend\Model\Config\Source\Locale;

class Weekdaycodes implements \Magento\Framework\Option\ArrayInterface
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
        return $this->_localeLists->getOptionWeekdays(true, true);
    }
}
