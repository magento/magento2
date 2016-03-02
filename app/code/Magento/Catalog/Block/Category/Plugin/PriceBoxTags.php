<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Category\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;

class PriceBoxTags
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * PriceBoxTags constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param StoreManager $storeManager
     * @param Session $customerSession
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        StoreManager $storeManager,
        Session $customerSession
    ) {
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param PriceBox $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKey(PriceBox $subject, $result)
    {
        return implode(
            '-',
            [
                $result,
                $this->priceCurrency->getCurrencySymbol(),
                $this->dateTime->scopeDate($this->storeManager->getStore()->getId())->format('Ymd'),
                $this->storeManager->getStore()->getId(),
                $this->customerSession->getCustomerGroupId(),
            ]
        );
    }
}
