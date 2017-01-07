<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Message\Notification;

/**
 * This class allows to display notification in the admin panel about possible discount errors.
 *
 * Discount errors may be caused by tax settings misconfiguration.
 */
class DiscountErrors implements \Magento\Tax\Model\System\Message\NotificationInterface
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

    /*
     * Websites with invalid discount settings
     *
     * @var array
     */
    private $storesWithInvalidSettings;

    /**
     * Initialize dependencies
     *
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
        return 'TAX_NOTIFICATION_DISCOUNT_ERRORS';
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        // Check if we are ignoring all notifications
        if ($this->taxConfig->isWrongDiscountSettingsIgnored()) {
            return false;
        }

        $this->storesWithInvalidSettings = $this->getStoresWithWrongSettings();

        // Check if we have valid tax notifications
        if ((!empty($this->storesWithInvalidSettings) && !$this->taxConfig->isWrongDiscountSettingsIgnored())) {
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

        if (!empty($this->storesWithInvalidSettings) && !$this->taxConfig->isWrongDiscountSettingsIgnored()) {
            $messageDetails .= '<strong>';
            $messageDetails .= __(
                'Warning tax discount configuration might result in different discounts
                                than a customer might expect. '
            );
            $messageDetails .= '</strong><p>';
            $messageDetails .= __('Store(s) affected: ');
            $messageDetails .= implode(', ', $this->storesWithInvalidSettings);
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'Click on the link to <a href="%1">ignore this notification</a>',
                $this->urlBuilder->getUrl('tax/tax/ignoreTaxNotification', ['section' => 'discount'])
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
     * Check if tax discount settings are compatible
     *
     * Matrix for invalid discount settings is as follows:
     *      Before Discount / Excluding Tax
     *      Before Discount / Including Tax
     *
     * @param null|int|bool|string|\Magento\Store\Model\Store $store $store
     * @return bool
     */
    private function checkDiscountSettings($store = null)
    {
        return $this->taxConfig->applyTaxAfterDiscount($store);
    }

    /**
     * Return list of store names where tax discount settings are compatible.
     * Return true if settings are wrong for default store.
     *
     * @return array
     */
    private function getStoresWithWrongSettings()
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
