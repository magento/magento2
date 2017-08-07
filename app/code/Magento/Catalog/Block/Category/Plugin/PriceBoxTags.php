<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Category\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;

/**
 * Class \Magento\Catalog\Block\Category\Plugin\PriceBoxTags
 *
 * @since 2.1.0
 */
class PriceBoxTags
{
    /**
     * @var TimezoneInterface
     * @since 2.1.0
     */
    protected $dateTime;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.1.0
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface
     * @since 2.1.0
     */
    private $priceCurrency;
    
    /**
     * @var ScopeResolverInterface
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @var TaxCalculation
     * @since 2.1.0
     */
    private $taxCalculation;

    /**
     * PriceBoxTags constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     * @since 2.1.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession
    ) {
        $this->dateTime = $dateTime;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param PriceBox $subject
     * @param string $result
     * @return string
     * @since 2.1.0
     */
    public function afterGetCacheKey(PriceBox $subject, $result)
    {
        return implode(
            '-',
            [
                $result,
                $this->priceCurrency->getCurrencySymbol(),
                $this->dateTime->scopeDate($this->scopeResolver->getScope()->getId())->format('Ymd'),
                $this->scopeResolver->getScope()->getId(),
                $this->customerSession->getCustomerGroupId(),
                $this->getTaxRateIds($subject),
            ]
        );
    }

    /**
     * @param PriceBox $subject
     * @return string
     * @since 2.1.0
     */
    private function getTaxRateIds(PriceBox $subject)
    {
        $rateIds = [];

        $customerSession = $this->customerSession;
        $billingAddress = $customerSession->getDefaultTaxBillingAddress();
        $shippingAddress = $customerSession->getDefaultTaxShippingAddress();
        $customerTaxClassId = $customerSession->getCustomerTaxClassId();

        if (!empty($billingAddress)) {
            $billingAddress = new \Magento\Framework\DataObject($billingAddress);
        }
        if (!empty($shippingAddress)) {
            $shippingAddress = new \Magento\Framework\DataObject($shippingAddress);
        }

        if (!empty($billingAddress) || !empty($shippingAddress)) {
            $rateRequest = $this->getTaxCalculation()->getRateRequest(
                $billingAddress,
                $shippingAddress,
                $customerTaxClassId,
                $this->scopeResolver->getScope()->getId(),
                $this->customerSession->getCustomerId()
            );

            $rateRequest->setProductClassId($subject->getSaleableItem()->getTaxClassId());
            $rateIds = $this->getTaxCalculation()->getResource()->getRateIds($rateRequest);
        }

        return implode('_', $rateIds);
    }

    /**
     * Get the TaxCalculation model
     *
     * @return \Magento\Tax\Model\Calculation
     *
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getTaxCalculation()
    {
        if ($this->taxCalculation === null) {
            $this->taxCalculation = \Magento\Framework\App\ObjectManager::getInstance()->get(TaxCalculation::class);
        }
        return $this->taxCalculation;
    }
}
