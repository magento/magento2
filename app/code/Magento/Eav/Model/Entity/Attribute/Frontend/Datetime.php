<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Frontend;

class Datetime extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory $attrBooleanFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory $attrBooleanFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        parent::__construct($attrBooleanFactory);
        $this->_localeDate = $localeDate;
    }

    /**
     * Retrieve attribute value
     *
     * @param \Magento\Framework\Object $object
     * @return mixed
     */
    public function getValue(\Magento\Framework\Object $object)
    {
        $data = '';
        $value = parent::getValue($object);
        $format = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM);

        if ($value) {
            try {
                $data = $this->_localeDate->date($value, \Zend_Date::ISO_8601, null, false)->toString($format);
            } catch (\Exception $e) {
                $data = $this->_localeDate->date($value, null, null, false)->toString($format);
            }
        }

        return $data;
    }
}
