<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Prepare date for save in DB
 *
 * @api
 * @since 100.0.2
 */
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
        $_formated = $object->getData($attributeName . '_is_formated');
        if (!$_formated && $object->hasData($attributeName)) {
            try {
                $value = $this->formatDate($object->getData($attributeName));
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid date'));
            }

            if ($value === null) {
                $value = $object->getData($attributeName);
            }

            $object->setData($attributeName, $value);
            $object->setData($attributeName . '_is_formated', true);
        }

        $defaultValue = $this->getDefaultValue();
        if ($object->getData($attributeName) === null
            && $defaultValue !== null
            && !$object->hasData($attributeName)) {
            $object->setData($attributeName, $defaultValue);
            $object->setData($attributeName . '_is_formated', true);
        }

        return $this;
    }

    /**
     * Prepare date for save in DB
     *
     * String format is used in input fields (all date input fields need apply locale settings)
     * int (Unix) format can be used in other parts of the code
     *
     * @param string|int|\DateTimeInterface $date
     * @return string
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // Unix timestamp given - simply instantiate date object
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTimeInterface)) {
            // normalized format expecting Y-m-d[ H:i:s]  - time is optional
            $date = new \DateTime($date);
        }
        return $date->format('Y-m-d H:i:s');
    }
}
