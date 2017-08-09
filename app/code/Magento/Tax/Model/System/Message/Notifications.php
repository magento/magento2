<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message;

/**
 * Notifications class
 */
class Notifications implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated 100.2.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * Stores with invalid display settings
     *
     * @var array
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors
     */
    protected $storesWithInvalidDisplaySettings;

    /**
     * Websites with invalid discount settings
     *
     * @var array
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\DiscountErrors
     */
    protected $storesWithInvalidDiscountSettings;

    /**
     * @var NotificationInterface[]
     */
    private $notifications = [];

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param NotificationInterface[] $notifications
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Tax\Model\Config $taxConfig,
        $notifications = []
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->taxConfig = $taxConfig;
        $this->notifications = $notifications;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getIdentity()
    {
        return md5('TAX_NOTIFICATION');
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        foreach ($this->notifications as $notification) {
            if ($notification->isDisplayed()) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        $messageDetails = '';

        foreach ($this->notifications as $notification) {
            $messageDetails .= $notification->getText();
        }

        $messageDetails .= '<p>';
        $messageDetails .= __('Please see <a href="%1">documentation</a> for more details. ', $this->getInfoUrl());
        $messageDetails .= __(
            'Click here to go to <a href="%1">Tax Configuration</a> and change your settings.',
            $this->getManageUrl()
        );
        $messageDetails .= '</p>';

        return $messageDetails;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * Get URL for the tax notification documentation
     *
     * @return string
     */
    public function getInfoUrl()
    {
        return $this->taxConfig->getInfoUrl();
    }

    /**
     * Get URL to the admin tax configuration page
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/tax');
    }

    /**
     * Check if tax calculation type and price display settings are compatible
     *
     * Invalid settings if
     *      Tax Calculation Method Based On 'Total' or 'Row'
     *      and at least one Price Display Settings has 'Including and Excluding Tax' value
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors::checkSettings
     */
    public function checkDisplaySettings($store = null)
    {
        if ($this->taxConfig->getAlgorithm($store) == \Magento\Tax\Model\Calculation::CALC_UNIT_BASE) {
            return true;
        }
        return $this->taxConfig->getPriceDisplayType($store) != \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH
            && $this->taxConfig->getShippingPriceDisplayType($store) != \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH
            && !$this->taxConfig->displayCartPricesBoth($store)
            && !$this->taxConfig->displayCartSubtotalBoth($store)
            && !$this->taxConfig->displayCartShippingBoth($store)
            && !$this->taxConfig->displaySalesPricesBoth($store)
            && !$this->taxConfig->displaySalesSubtotalBoth($store)
            && !$this->taxConfig->displaySalesShippingBoth($store);
    }

    /**
     * Check if tax discount settings are compatible
     *
     * Matrix for invalid discount settings is as follows:
     *      Before Discount / Excluding Tax
     *      Before Discount / Including Tax
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\DiscountErrors::checkSettings
     */
    public function checkDiscountSettings($store = null)
    {
        return $this->taxConfig->applyTaxAfterDiscount($store);
    }

    /**
     * Get URL to ignore tax notifications
     *
     * @param string $section
     * @return string
     * @deprecated 100.2.0
     */
    public function getIgnoreTaxNotificationUrl($section)
    {
        return $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => $section]);
    }

    /**
     * Return list of store names which have not compatible tax calculation type and price display settings.
     * Return true if settings are wrong for default store.
     *
     * @return array
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors::getStoresWithWrongSettings
     */
    public function getStoresWithWrongDisplaySettings()
    {
        $storeNames = [];
        $storeCollection = $this->storeManager->getStores(true);
        foreach ($storeCollection as $store) {
            if (!$this->checkDisplaySettings($store)) {
                $website = $store->getWebsite();
                $storeNames[] = $website->getName() . '(' . $store->getName() . ')';
            }
        }
        return $storeNames;
    }

    /**
     * Return list of store names where tax discount settings are compatible.
     * Return true if settings are wrong for default store.
     *
     * @return array
     * @deprecated 100.2.0
     * @see \Magento\Tax\Model\System\Message\Notification\DiscountErrors::getStoresWithWrongSettings
     */
    public function getStoresWithWrongDiscountSettings()
    {
        $storeNames = [];
        $storeCollection = $this->storeManager->getStores(true);
        foreach ($storeCollection as $store) {
            if (!$this->checkDiscountSettings($store)) {
                $website = $store->getWebsite();
                $storeNames[] = $website->getName() . '(' . $store->getName() . ')';
            }
        }
        return $storeNames;
    }
}
