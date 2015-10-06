<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type;

use Magento\Framework\Stdlib\DateTime;

/**
 * Catalog product option date validator
 */
class Date extends \Magento\Catalog\Model\Product\Option\Type\Date
{
    /**
     * @var string
     */
    protected $_formattedOptionValue = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        array $data = []
    ) {
        $this->_localeDate = $localeDate;
        parent::__construct($checkoutSession, $scopeConfig, $data);
    }

    /**
     * @param array $values
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateUserValue($values)
    {
        $this->_checkoutSession->setUseNotice(false);

        $this->setIsValid(false);

        $option = $this->getOption();
        if (!isset($values[$option->getId()]) && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            throw new LocalizedException(__('Please specify product\'s required option(s).'));
        } elseif (isset($values[$option->getId()])) {
            $this->setUserValue($values[$option->getId()]);
            $this->setIsValid(true);
        }

        $option = $this->getOption();
        $value = $this->getUserValue();
        $dateTime = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $value);

        $dateValid = true;
        $lastErrors = \DateTime::getLastErrors();
        if (!($dateTime && $lastErrors['error_count'] == 0)) {
            $dateValid = false;
        }

        if ($dateValid && $dateTime) {
            $this->setUserValue(
                [
                    'date' => $value,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'day' => $dateTime->format('d'),
                    'hour' => $dateTime->format('H'),
                    'minute' => intval($dateTime->format('i')),
                    'day_part' => $dateTime->format('a'),
                    'date_internal' => '',
                ]
            );
        } elseif (!$dateValid && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please specify product\'s required option(s).')
            );
        } else {
            $this->setUserValue(null);
            return $this;
        }

        return $this;
    }

}
