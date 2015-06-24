<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model\App\Action;

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
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Weee\Model\Resource\Tax
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
     * @param \Magento\Weee\Model\Resource\WeeeTax $weeeTax
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
        \Magento\Weee\Model\Resource\Tax $weeeTax,
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
            !$this->cacheConfig->isEnabled() ||
            !$this->weeeHelper->isEnabled()) {
            return $proceed($request);
        }

        $basedOn = $this->taxHelper->getTaxBasedOn();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

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

        if ($basedOn == 'default') {
            $countryId = $defaultCountryId;
            $regionId = $defaultRegionId;
        } else if ($basedOn == 'origin') {
            $countryId = $this->scopeConfig->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
            $regionId = $this->scopeConfig->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            );
        } else if ($basedOn == 'shipping') {
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

        if (!$countryId && !$regionId) {
            // country and region does not exist
            return $proceed($request);
        } else if ($countryId && !$regionId) {
            // country exist and region does not exist
            $exist = $this->weeeTax->isWeeeInLocation(
                $countryId,
                $regionId,
                $websiteId
            );
            if ($exist) {
                $this->httpContext->setValue(
                    'weee_taxes',
                    ['countryId' => $countryId, 'regionId' => $regionId],
                    0
                );
            }
        } else {
            // country and region exist
            $exist = $this->weeeTax->isWeeeInLocation(
                $countryId,
                $regionId,
                $websiteId
            );
            if ($exist) {
                $this->httpContext->setValue(
                    'weee_taxes',
                    ['countryId' => $countryId, 'regionId' => $regionId],
                    0
                );
            } else {
                // just check the country for weee
                $regionId = 0;
                $exist = $this->weeeTax->isWeeeInLocation(
                    $countryId,
                    $regionId,
                    $websiteId
                );
                if ($exist) {
                    $this->httpContext->setValue(
                        'weee_taxes',
                        ['countryId' => $countryId, 'regionId' => $regionId],
                        0
                    );
                }
            }
        }

        return $proceed($request);
    }
}
