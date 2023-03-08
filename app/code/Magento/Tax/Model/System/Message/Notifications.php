<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Notifications class
 */
class Notifications implements MessageInterface
{
    /**
     * Stores with invalid display settings
     *
     * @var array
     * @deprecated 100.1.0
     * @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors
     */
    protected $storesWithInvalidDisplaySettings;

    /**
     * Websites with invalid discount settings
     *
     * @var array
     * @deprecated 100.1.0
     * @see \Magento\Tax\Model\System\Message\Notification\DiscountErrors
     */
    protected $storesWithInvalidDiscountSettings;

    /**
     * @param StoreManagerInterface $storeManager Store manager object @deprecated 100.1.0
     * @param UrlInterface $urlBuilder
     * @param TaxConfig $taxConfig Tax configuration object
     * @param NotificationInterface[] $notifications
     * @param Escaper|null $escaper
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly UrlInterface $urlBuilder,
        protected readonly TaxConfig $taxConfig,
        private $notifications = [],
        private ?Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getIdentity()
    {
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('TAX_NOTIFICATION');
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
        return $this->escaper->escapeUrl($this->taxConfig->getInfoUrl());
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
     * @param null|int|bool|string|Store $store $store
     * @return bool
     * @deprecated 100.1.3
     * @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors::checkSettings
     */
    public function checkDisplaySettings($store = null)
    {
        if ($this->taxConfig->getAlgorithm($store) == Calculation::CALC_UNIT_BASE) {
            return true;
        }
        return $this->taxConfig->getPriceDisplayType($store) != TaxConfig::DISPLAY_TYPE_BOTH
            && $this->taxConfig->getShippingPriceDisplayType($store) != TaxConfig::DISPLAY_TYPE_BOTH
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
     * @param null|int|bool|string|Store $store $store
     * @return bool
     * @deprecated 100.1.3
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
     * @deprecated 100.1.3
     */
    public function getIgnoreTaxNotificationUrl($section)
    {
        return $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => $section]);
    }

    /**
     * Return list of store names which have not compatible tax calculation type and price display settings.
     *
     * Return true if settings are wrong for default store.
     *
     * @return array
     * @deprecated 100.1.3
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
     *
     * Return true if settings are wrong for default store.
     *
     * @return array
     * @deprecated 100.1.3
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
