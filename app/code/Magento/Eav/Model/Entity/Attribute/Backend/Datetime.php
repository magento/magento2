<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Model\Entity\Attribute\Backend;

class Datetime extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @codeCoverageIgnore
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
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $attributeValue = $object->getData($attributeName);
        if ($object->hasData($attributeName)) {
            // format only date that is not formatted yet
            $dateFormatted = $this->dateIsFormatted($attributeValue);
            if (!$dateFormatted) {
                try {
                    $value = $this->formatDate($attributeValue);
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Invalid date'));
                }

                if (is_null($value)) {
                    $value = $attributeValue;
                }

                $object->setData($attributeName, $value);
            }
        }

        return $this;
    }

    /**
     * Check if date is formatted
     *
     * @param string|\DateTime $attributeValue
     * @return bool
     */
    private function dateIsFormatted($attributeValue)
    {
        $pattern = '/(\d{4})-(\d{2})-(\d{2})(\s(\d{2}):(\d{2}):(\d{2}))?/';
        if ($attributeValue instanceof \DateTime) {
            return false;
        } elseif (preg_match($pattern, $attributeValue)) {
            return true;
        }
        return false;
    }

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this means that we use valid date)
     *
     * @param string|int|\DateTime $date
     * @return string
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTime)) {
            $date = $this->_localeDate->date($date, null, false);
        }
        // normalized format expecting Y-m-d [H:i:s] - time is optional
        return $date->format('Y-m-d H:i:s');
    }
}
