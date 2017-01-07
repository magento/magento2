<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message\Notification;

/**
 * This class allows to display notification in the admin panel about possible rounding errors.
 *
 * Rounding errors may be caused by tax settings misconfiguration.
 */
class RoundingErrors implements \Magento\Tax\Model\System\Message\NotificationInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * Stores with invalid display settings
     *
     * @var array
     */
    private $storesWithInvalidSettings;

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
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        return 'TAX_NOTIFICATION_ROUNDING_ERRORS';
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        // Check if we are ignoring all notifications
        if ($this->taxConfig->isWrongDisplaySettingsIgnored()) {
            return false;
        }

        $this->storesWithInvalidSettings = $this->getStoresWithWrongSettings();

        // Check if we have valid tax notifications
        if ((!empty($this->storesWithInvalidSettings) && !$this->taxConfig->isWrongDisplaySettingsIgnored())) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        $messageDetails = '';

        if (!empty($this->storesWithInvalidSettings) && !$this->taxConfig->isWrongDisplaySettingsIgnored()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('Warning tax configuration can result in rounding errors. ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->storesWithInvalidSettings);
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => 'price_display'])
            );
            $messageDetails .= "</p>";
        }

        return $messageDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
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
    private function checkDisplaySettings($store = null)
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
     * Return list of store names which have not compatible tax calculation type and price display settings.
     * Return true if settings are wrong for default store.
     *
     * @return array
     */
    private function getStoresWithWrongSettings()
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
}
