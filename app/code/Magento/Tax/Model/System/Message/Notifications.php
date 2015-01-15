<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    /*
     * Stores with invalid display settings
     *
     * @var array
     */
    protected $storesWithInvalidDisplaySettings;

    /*
     * Websites with invalid discount settings
     *
     * @var array
     */
    protected $storesWithInvalidDiscountSettings;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('TAX_NOTIFICATION');
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
     */
    public function checkDiscountSettings($store = null)
    {
        return $this->taxConfig->applyTaxAfterDiscount($store);
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
     * Get URL to ignore tax notifications
     *
     * @param string $section
     * @return string
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

    /**
     * Check whether notification is displayed
     * Checks if any of these settings are being ignored or valid:
     *      1. Wrong discount settings
     *      2. Wrong display settings
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // Check if we are ignoring all notifications
        if ($this->taxConfig->isWrongDisplaySettingsIgnored() && $this->taxConfig->isWrongDiscountSettingsIgnored()) {
            return false;
        }

        $this->storesWithInvalidDisplaySettings = $this->getStoresWithWrongDisplaySettings();
        $this->storesWithInvalidDiscountSettings = $this->getStoresWithWrongDiscountSettings();

        // Check if we have valid tax notifications
        if ((!empty($this->storesWithInvalidDisplaySettings) && !$this->taxConfig->isWrongDisplaySettingsIgnored())
            || (!empty($this->storesWithInvalidDiscountSettings) && !$this->taxConfig->isWrongDiscountSettingsIgnored())
            ) {
            return true;
        }

        return false;
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        $messageDetails = '';

        if (!empty($this->storesWithInvalidDisplaySettings) && !$this->taxConfig->isWrongDisplaySettingsIgnored()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('Warning tax configuration can result in rounding errors. ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->storesWithInvalidDisplaySettings);
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->getIgnoreTaxNotificationUrl('price_display')
            );
            $messageDetails .= "</p>";
        }

        if (!empty($this->storesWithInvalidDiscountSettings) && !$this->taxConfig->isWrongDiscountSettingsIgnored()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __(
                'Warning tax discount configuration might result in different discounts
                                than a customer might expect. '
            );
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->storesWithInvalidDiscountSettings);
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->getIgnoreTaxNotificationUrl('discount')
            );
            $messageDetails .= "</p>";
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
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
