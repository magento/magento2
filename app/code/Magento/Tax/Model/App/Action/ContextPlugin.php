<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\App\Action;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\GroupManagement;

/**
 * Class ContextPlugin
 */
class ContextPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @var \Magento\Tax\Model\Calculation\Proxy
     */
    protected $taxCalculation;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * Cache config
     *
     * @var \Magento\PageCache\Model\Config
     */
    private $cacheConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Tax\Model\Calculation\Proxy $calculation
     * @param \Magento\Weee\Model\WeeeTax $weeeTax
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\PageCache\Model\Config $cacheConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Tax\Model\Calculation\Proxy $calculation,
        \Magento\Weee\Model\Tax $weeeTax,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\PageCache\Model\Config $cacheConfig
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->taxCalculation = $calculation;
        $this->weeeTax = $weeeTax;
        $this->taxHelper = $taxHelper;
        $this->weeeHelper = $weeeHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param \Magento\Framework\App\Action\Action $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\Action\Action $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->moduleManager->isEnabled('Magento_PageCache') ||
            !$this->cacheConfig->isEnabled() ) {
            return $proceed($request);
        }

        if ($this->taxHelper->isCatalogPriceDisplayAffectedByTax() || $this->weeeHelper->isEnabled())
        {
            $defaultBillingAddress = $this->customerSession->getDefaultTaxBillingAddress();
            $defaultShippingAddress = $this->customerSession->getDefaultTaxShippingAddress();
            $websiteId = $this->customerSession->getWebsiteId();
            $customerTaxClassId = $this->customerSession->getCustomerTaxClassId();
        }

        if ($this->taxHelper->isCatalogPriceDisplayAffectedByTax())
        {
            if (!empty($defaultBillingAddress) || !empty($defaultShippingAddress)) {
                $taxRates = $this->taxCalculation->getTaxRates(
                    $defaultBillingAddress,
                    $defaultShippingAddress,
                    $customerTaxClassId
                );
                $this->httpContext->setValue(
                    'tax_rates',
                    $taxRates,
                    0
                );
            }
        }

        if ($this->weeeHelper->isEnabled()) {
            if (!empty($defaultBillingAddress)) {
                $countryId = $defaultBillingAddress['country_id'];
                $regionId = $defaultBillingAddress['region_id'];
                $weeeTaxes = $this->weeeTax->getWeeeAttributes(
                    $countryId,
                    $regionId,
                    $websiteId
                );
                $this->httpContext->setValue(
                    'weee_taxes',
                    $weeeTaxes,
                    0
                );
            }
        }

        return $proceed($request);
    }
}
