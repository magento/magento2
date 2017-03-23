<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
     * @codeCoverageIgnore
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
     * @param \Magento\Framework\DataObject $object
     * @return mixed
     */
    public function getValue(\Magento\Framework\DataObject $object)
    {
        $data = '';
        $value = parent::getValue($object);

        if ($value) {
            $data = $this->_localeDate->formatDateTime(
                new \DateTime($value),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE
            );
        }

        return $data;
    }
}
