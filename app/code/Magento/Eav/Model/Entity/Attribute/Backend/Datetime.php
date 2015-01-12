<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

use Magento\Eav\Exception as EavException;

class Datetime extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->_localeDate = $localeDate;
    }

    /**
     * Formatting date value before save
     *
     * Should set (bool, string) correct type for empty value from html form,
     * necessary for further process, else date string
     *
     * @param \Magento\Framework\Object $object
     * @throws EavException
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $_formated = $object->getData($attributeName . '_is_formated');
        if (!$_formated && $object->hasData($attributeName)) {
            try {
                $value = $this->formatDate($object->getData($attributeName));
            } catch (\Exception $e) {
                throw new EavException(__('Invalid date'));
            }

            if (is_null($value)) {
                $value = $object->getData($attributeName);
            }

            $object->setData($attributeName, $value);
            $object->setData($attributeName . '_is_formated', true);
        }

        return $this;
    }

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this meen whot we use valid date)
     *
     * @param string|int $date
     * @return string
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (preg_match('/^[0-9]+$/', $date)) {
            $date = new \Magento\Framework\Stdlib\DateTime\Date((int)$date);
            // international format
        } elseif (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) {
            $zendDate = new \Magento\Framework\Stdlib\DateTime\Date();
            $date = $zendDate->setIso($date);
            // parse this date in current locale, do not apply GMT offset
        } else {
            $date = $this->_localeDate->date(
                $date,
                $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT),
                null,
                false
            );
        }
        return $date->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
    }
}
