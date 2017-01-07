<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message\Notification;

use Magento\Tax\Model\Config;

/**
 * Class allows to show admin notification about possible issues related to tax calculation and display settings.
 *
 * For the case when prices under "Calculation Settings" are set to "Excluding Tax"
 * and at the same time some of price display settings are set to "Including Tax"
 */
class AdminPriceExcludingTax implements \Magento\Tax\Model\System\Message\NotificationInterface
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
     * @var Config
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
     * @param Config $taxConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        Config $taxConfig
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
        return 'TAX_NOTIFICATION_PRICE_EXCLUDING_TAX';
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        if (!$this->taxConfig->isWrongPriceExcludingTaxSettingsIgnored() && $this->getStoresWithWrongSettings()) {
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

        if ($this->isDisplayed()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('Current tax configuration can result in rounding errors and discount calculation errors. ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->getStoresWithWrongSettings());
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => 'price_excluding_tax'])
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
     * Return list of store names which have incompatible tax calculation and tax display settings.
     *
     * @return array
     */
    private function getStoresWithWrongSettings()
    {
        if (null !== $this->storesWithInvalidSettings) {
            return $this->storesWithInvalidSettings;
        }
        $this->storesWithInvalidSettings = [];
        $storeCollection = $this->storeManager->getStores(true);
        foreach ($storeCollection as $store) {
            if (!$this->checkSettings($store)) {
                $website = $store->getWebsite();
                $this->storesWithInvalidSettings[] = $website->getName() . '(' . $store->getName() . ')';
            }
        }
        return $this->storesWithInvalidSettings;
    }

    /**
     * Check if tax calculation and tax display settings are compatible.
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool false if settings are incorrect
     */
    private function checkSettings($store = null)
    {
        $adminCatalogPricesExcludeTax = !$this->taxConfig->priceIncludesTax($store)
            || !$this->taxConfig->shippingPriceIncludesTax($store);
        $displayPricesWhichIncludeTax = $this->hasCatalogDisplaySettingsNotIncludingTax($store)
            || $this->hasCartDisplaySettingsNotIncludingTax($store)
            || $this->hasSalesDisplaySettingsNotIncludingTax($store);
        return !($adminCatalogPricesExcludeTax && $displayPricesWhichIncludeTax);
    }

    /**
     * Check if there are any price display settings for catalog with values other than "Including tax"
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     */
    private function hasCatalogDisplaySettingsNotIncludingTax($store = null)
    {
        return $this->taxConfig->getPriceDisplayType($store) !== Config::DISPLAY_TYPE_EXCLUDING_TAX
            || $this->taxConfig->getShippingPriceDisplayType($store) !== Config::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Check if there are any price display settings for cart with values other than "Including tax"
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     */
    private function hasCartDisplaySettingsNotIncludingTax($store = null)
    {
        return !$this->taxConfig->displayCartPricesInclTax($store)
            || !$this->taxConfig->displayCartSubtotalInclTax($store)
            || !$this->taxConfig->displayCartShippingInclTax($store)
            || !$this->taxConfig->displayCartDiscountInclTax($store);
    }

    /**
     * Check if there are any price display settings for orders, invoices, credit memos with values not "Including tax"
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     */
    private function hasSalesDisplaySettingsNotIncludingTax($store = null)
    {
        return !$this->taxConfig->displaySalesPricesInclTax($store)
            || !$this->taxConfig->displaySalesSubtotalInclTax($store)
            || !$this->taxConfig->displaySalesShippingInclTax($store)
            || !$this->taxConfig->displaySalesDiscountInclTax($store);
    }
}
