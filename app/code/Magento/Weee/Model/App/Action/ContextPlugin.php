<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model\App\Action;

/**
 * Class ContextPlugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $cacheConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Weee\Model\Tax $weeeTax
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\PageCache\Model\Config $cacheConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Weee\Model\Tax $weeeTax,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Weee\Helper\Data $weeeHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\PageCache\Model\Config $cacheConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->weeeTax = $weeeTax;
        $this->taxHelper = $taxHelper;
        $this->weeeHelper = $weeeHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->weeeHelper->isEnabled() ||
            !$this->customerSession->isLoggedIn() ||
            !$this->moduleManager->isEnabled('Magento_PageCache') ||
            !$this->cacheConfig->isEnabled()) {
            return $proceed($request);
        }

        $basedOn = $this->taxHelper->getTaxBasedOn();
        if ($basedOn != 'shipping' && $basedOn != 'billing') {
            return $proceed($request);
        }

        $weeeTaxRegion = $this->getWeeeTaxRegion($basedOn);
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $countryId = $weeeTaxRegion['countryId'];
        $regionId = $weeeTaxRegion['regionId'];

        if (!$countryId && !$regionId) {
            // country and region does not exist
            return $proceed($request);
        } else if ($countryId && !$regionId) {
            // country exist and region does not exist
            $regionId = 0;
            $exist = $this->weeeTax->isWeeeInLocation(
                $countryId,
                $regionId,
                $websiteId
            );
        } else {
            // country and region exist
            $exist = $this->weeeTax->isWeeeInLocation(
                $countryId,
                $regionId,
                $websiteId
            );
            if (!$exist) {
                // just check the country for weee
                $regionId = 0;
                $exist = $this->weeeTax->isWeeeInLocation(
                    $countryId,
                    $regionId,
                    $websiteId
                );
            }
        }

        if ($exist) {
            $this->httpContext->setValue(
                'weee_tax_region',
                ['countryId' => $countryId, 'regionId' => $regionId],
                0
            );
        }
        return $proceed($request);
    }

    /**
     * @param string $basedOn
     * @return array
     */
    protected function getWeeeTaxRegion($basedOn)
    {
        $countryId = null;
        $regionId = null;
        $defaultCountryId = $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
        $defaultRegionId = $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );

        if ($basedOn == 'shipping') {
            $defaultShippingAddress = $this->customerSession->getDefaultTaxShippingAddress();
            if (empty($defaultShippingAddress)) {
                $countryId = $defaultCountryId;
                $regionId = $defaultRegionId;
            } else {
                $countryId = $defaultShippingAddress['country_id'];
                $regionId = $defaultShippingAddress['region_id'];
            }

        } else if ($basedOn == 'billing') {
            $defaultBillingAddress = $this->customerSession->getDefaultTaxBillingAddress();
            if (empty($defaultBillingAddress)) {
                $countryId = $defaultCountryId;
                $regionId = $defaultRegionId;
            } else {
                $countryId = $defaultBillingAddress['country_id'];
                $regionId = $defaultBillingAddress['region_id'];
            }
        }
        return ['countryId' => $countryId, 'regionId' => $regionId];
    }
}
