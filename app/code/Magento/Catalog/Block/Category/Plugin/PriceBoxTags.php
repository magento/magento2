<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Category\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\ResourceModel\Calculation as TaxCalculationResource;

/**
 * Plugin for catalog price box renderer
 */
class PriceBoxTags
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TaxCalculation
     */
    private $taxCalculation;

    /**
     * @var TaxCalculationResource
     */
    private $taxCalculationResource;

    /**
     * PriceBoxTags constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     * @param TaxCalculation $taxCalculation
     * @param TaxCalculationResource $taxCalculationResource
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession,
        TaxCalculation $taxCalculation,
        TaxCalculationResource $taxCalculationResource
    ) {
        $this->dateTime = $dateTime;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->scopeResolver = $scopeResolver;
        $this->taxCalculation = $taxCalculation;
        $this->taxCalculationResource = $taxCalculationResource;
    }

    /**
     * Add additional parts to price box cache key
     *
     * @param PriceBox $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetCacheKey(PriceBox $subject, $result)
    {
        return implode(
            '-',
            [
                $result,
                $this->priceCurrency->getCurrency()->getCode(),
                $this->dateTime->scopeDate($this->scopeResolver->getScope()->getId())->format('Ymd'),
                $this->scopeResolver->getScope()->getId(),
                $this->customerSession->getCustomerGroupId(),
                $this->getTaxRateIds($subject),
            ]
        );
    }

    /**
     * Get current tax rate ids as string
     *
     * @param PriceBox $subject
     *
     * @return string
     */
    private function getTaxRateIds(PriceBox $subject)
    {
        $rateIds = [];

        $customerSession = $this->customerSession;
        $billingAddress = $customerSession->getDefaultTaxBillingAddress();
        $shippingAddress = $customerSession->getDefaultTaxShippingAddress();
        $customerTaxClassId = $customerSession->getCustomerTaxClassId();

        if (!empty($billingAddress)) {
            $billingAddress = new DataObject($billingAddress);
        }
        if (!empty($shippingAddress)) {
            $shippingAddress = new DataObject($shippingAddress);
        }

        if (!empty($billingAddress) || !empty($shippingAddress)) {
            $rateRequest = $this->taxCalculation->getRateRequest(
                $shippingAddress,
                $billingAddress,
                $customerTaxClassId,
                $this->scopeResolver->getScope()->getId(),
                $this->customerSession->getCustomerId()
            );

            $rateRequest->setProductClassId($subject->getSaleableItem()->getTaxClassId());
            $rateIds = $this->taxCalculationResource->getRateIds($rateRequest);
        }

        return implode('_', $rateIds);
    }
}
