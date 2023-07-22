<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Block\Tracking;

use DateTime;
use IntlDateFormatter;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Shipping\Model\Info;
use Magento\Store\Model\ScopeInterface;

/**
 * Tracking popup
 *
 * @api
 * @since 100.0.2
 */
class Popup extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        protected readonly DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve array of tracking info
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        /* @var Info $info */
        $info = $this->_registry->registry('current_shipping_info');

        return $info->getTrackingInfo();
    }

    /**
     * Format given date and time in current locale without changing timezone
     *
     * @param string $date
     * @param string $time
     * @return string
     */
    public function formatDeliveryDateTime($date, $time)
    {
        return $this->formatDeliveryDate($date) . ' ' . $this->formatDeliveryTime($time);
    }

    /**
     * Format given date in current locale without changing timezone
     *
     * @param string $date
     * @return string
     */
    public function formatDeliveryDate($date)
    {
        $format = $this->_localeDate->getDateFormat(IntlDateFormatter::MEDIUM);
        return $this->dateTimeFormatter->formatObject($this->_localeDate->date(new DateTime($date)), $format);
    }

    /**
     * Format given time [+ date] in current locale without changing timezone
     *
     * @param string $time
     * @param string $date
     * @return string
     */
    public function formatDeliveryTime($time, $date = null)
    {
        if (!empty($date)) {
            $time = $date . ' ' . $time;
        }

        $format = $this->_localeDate->getTimeFormat(IntlDateFormatter::SHORT);
        return $this->dateTimeFormatter->formatObject($this->_localeDate->date(new DateTime($time)), $format);
    }

    /**
     * Is 'contact us' option enabled?
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getContactUsEnabled()
    {
        return $this->_scopeConfig->isSetFlag(
            'contact/contact/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get support email
     *
     * @return string
     */
    public function getStoreSupportEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_support/email',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get contact us url
     *
     * @return string
     */
    public function getContactUs()
    {
        return $this->getUrl('contact');
    }
}
