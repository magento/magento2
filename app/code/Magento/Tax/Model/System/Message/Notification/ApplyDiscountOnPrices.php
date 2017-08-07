<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message\Notification;

use Magento\Tax\Model\Config;

/**
 * Class allows to show admin notification about possible issues related to "Apply Discount On Prices" setting.
 *
 * Warning is displayed in case when "Catalog Prices" = "Excluding Tax"
 * AND "Apply Discount On Prices" = "Including Tax"
 * AND "Apply Customer Tax" = "After Discount"
 * @since 2.2.0
 */
class ApplyDiscountOnPrices implements \Magento\Tax\Model\System\Message\NotificationInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $taxConfig;

    /**
     * Stores with invalid display settings
     *
     * @var array
     * @since 2.2.0
     */
    private $storesWithInvalidSettings;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param Config $taxConfig
     * @since 2.2.0
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
     * @codeCoverageIgnore
     * @since 2.2.0
     */
    public function getIdentity()
    {
        return 'TAX_NOTIFICATION_APPLY_DISCOUNT';
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isDisplayed()
    {
        if (!$this->taxConfig->isWrongApplyDiscountSettingIgnored() && $this->getStoresWithWrongSettings()) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getText()
    {
        $messageDetails = '';

        if ($this->isDisplayed()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('To apply the discount on prices including tax and apply the tax after discount,'
                . ' set Catalog Prices to “Including Tax”. ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->getStoresWithWrongSettings());
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => 'apply_discount'])
            );
            $messageDetails .= "</p>";
        }

        return $messageDetails;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.2.0
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * Return list of store names which have invalid settings.
     *
     * @return array
     * @since 2.2.0
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

    /**
     * Check if settings are valid.
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool false if settings are incorrect
     * @since 2.2.0
     */
    private function checkSettings($store = null)
    {
        return $this->taxConfig->priceIncludesTax($store)
            || !$this->taxConfig->applyTaxAfterDiscount($store)
            || !$this->taxConfig->discountTax($store);
    }
}
