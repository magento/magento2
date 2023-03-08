<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message\Notification;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\System\Message\NotificationInterface;

/**
 * This class allows to display notification in the admin panel about possible rounding errors.
 *
 * Rounding errors may be caused by tax settings misconfiguration.
 */
class RoundingErrors implements NotificationInterface
{
    /**
     * Stores with invalid display settings
     *
     * @var array
     */
    private $storesWithInvalidSettings;

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param Config $taxConfig
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly Config $taxConfig
    ) {
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
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
        if (!$this->taxConfig->isWrongDisplaySettingsIgnored() && $this->getStoresWithWrongSettings()) {
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

        if (!empty($this->getStoresWithWrongSettings()) && !$this->taxConfig->isWrongDisplaySettingsIgnored()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('Your current tax configuration may result in rounding errors. ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->getStoresWithWrongSettings());
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
     * @codeCoverageIgnore
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
     * @param null|int|bool|string|Store $store $store
     * @return bool
     */
    private function checkSettings($store = null)
    {
        if ($this->taxConfig->getAlgorithm($store) == Calculation::CALC_UNIT_BASE) {
            return true;
        }
        return $this->taxConfig->getPriceDisplayType($store) != Config::DISPLAY_TYPE_BOTH
            && $this->taxConfig->getShippingPriceDisplayType($store) != Config::DISPLAY_TYPE_BOTH
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
        if (null !== $this->storesWithInvalidSettings) {
            return $this->storesWithInvalidSettings;
        }
        $this->storesWithInvalidSettings = [];
        $storeCollection = $this->storeManager->getStores(true);
        foreach ($storeCollection as $store) {
            if (!$this->checkSettings($store)) {
                $website = $store->getWebsite();
                $this->storesWithInvalidSettings[] = $website->getName() . ' (' . $store->getName() . ')';
            }
        }
        return $this->storesWithInvalidSettings;
    }
}
