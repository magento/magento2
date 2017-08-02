<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Model\Entity\Attribute\Frontend;

/**
 * @api
 * @since 2.0.0
 */
class Datetime extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.0.0
     */
    protected $_localeDate;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory $attrBooleanFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
