<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

/**
 *
 * Special Start Date attribute backend
 *
 * @api
 *
 * @since 100.0.2
 */
class Startdate extends \Magento\Eav\Model\Entity\Attribute\Backend\Datetime
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_date = $date;
        parent::__construct($localeDate);
    }

    /**
     * Get attribute value for save.
     *
     * @param \Magento\Framework\DataObject $object
     * @return string|bool
     */
    protected function _getValueForSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $startDate = $object->getData($attributeName);

        return $startDate;
    }

    /**
     * Before save hook.
     *
     * Prepare attribute value for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $startDate = $this->_getValueForSave($object);
        if ($startDate === false) {
            return $this;
        }

        $object->setData($this->getAttribute()->getName(), $startDate);
        parent::beforeSave($object);
        return $this;
    }

    /**
     * Product from date attribute validate function.
     *
     * In case invalid data throws exception.
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     * @return bool
     */
    public function validate($object)
    {
        $attr = $this->getAttribute();
        $maxDate = $attr->getMaxValue();
        $startDate = $this->_getValueForSave($object);
        if ($startDate === false || $startDate === null) {
            return true;
        }

        if ($maxDate) {
            $date = $this->_date;
            $value = $date->timestamp($startDate);
            $maxValue = $date->timestamp($maxDate);

            if ($value > $maxValue) {
                $message = __('Make sure the To Date is later than or the same as the From Date.');
                $eavExc = new \Magento\Eav\Model\Entity\Attribute\Exception($message);
                $eavExc->setAttributeCode($attr->getName());
                throw $eavExc;
            }
        } elseif ($this->isInvertedWindow($object, $startDate)) {
            $eavExc = new \Magento\Eav\Model\Entity\Attribute\Exception(
                __('Make sure the To Date is later than or the same as the From Date.')
            );
            $eavExc->setAttributeCode($attr->getName());
            throw $eavExc;
        }
        return true;
    }

    /**
     * Whether the start date is a later day than the matching "to" date of the object
     *
     * Used when no max value was set: Adminhtml controllers set it explicitly, other save paths (REST, GraphQL, code)
     * rely on this. Only checked when the window is changed by this save, so stored legacy data does not block
     * unrelated updates (also when a payload re-sends them in another format); compared by calendar day, as the
     * "to" date is inclusive.
     *
     * @param \Magento\Framework\DataObject $object
     * @param mixed $startDate
     * @return bool
     */
    private function isInvertedWindow($object, $startDate): bool
    {
        $name = (string)$this->getAttribute()->getName();
        $endName = preg_replace('/_from(_date)?$/', '_to$1', $name);
        if ($endName === null || $endName === $name
            || !($this->isDayChanged($object, $name) || $this->isDayChanged($object, $endName))
        ) {
            return false;
        }
        $startDay = $this->getDay($startDate);
        $endDay = $this->getDay($object->getData($endName));

        return $startDay !== null && $endDay !== null && $startDay > $endDay;
    }

    /**
     * Whether the object's value for an attribute is on another calendar day than the loaded one
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $attributeCode
     * @return bool
     */
    private function isDayChanged($object, string $attributeCode): bool
    {
        return $this->getDay($object->getData($attributeCode)) !== $this->getDay($object->getOrigData($attributeCode));
    }

    /**
     * Calendar day (Y-m-d) a date value is stored as, or null for an empty, zero or unparsable value
     *
     * Uses formatDate(), so digits are read as a timestamp exactly as beforeSave() stores them. Unparsable values
     * return null: beforeSave() rejects their format.
     *
     * @param mixed $value
     * @return string|null
     */
    private function getDay($value): ?string
    {
        if (!$value instanceof \DateTimeInterface) {
            $value = (string)$value;
            if ($value === '' || str_starts_with($value, '0000-00-00')
                || (!preg_match('/^[0-9]+$/', $value) && date_create($value) === false)
            ) {
                return null;
            }
        }

        return substr((string)$this->formatDate($value), 0, 10) ?: null;
    }
}
