<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model\App\Action;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax;

/**
 * Plugin to provide Context information to Weee Action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextPlugin
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var WeeeHelper
     */
    protected $weeeHelper;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Tax
     */
    protected $weeeTax;

    /**
     * @var PageCacheConfig
     */
    protected $cacheConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     * @param Tax $weeeTax
     * @param TaxHelper $taxHelper
     * @param WeeeHelper $weeeHelper
     * @param ModuleManager $moduleManager
     * @param PageCacheConfig $cacheConfig
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerSession $customerSession,
        HttpContext $httpContext,
        Tax $weeeTax,
        TaxHelper $taxHelper,
        WeeeHelper $weeeHelper,
        ModuleManager $moduleManager,
        PageCacheConfig $cacheConfig,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
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
     * Before dispatch.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if (!$this->weeeHelper->isEnabled() ||
            !$this->customerSession->isLoggedIn() ||
            !$this->moduleManager->isEnabled('Magento_PageCache') ||
            !$this->cacheConfig->isEnabled()) {
            return;
        }

        $basedOn = $this->taxHelper->getTaxBasedOn();
        if ($basedOn != 'shipping' && $basedOn != 'billing') {
            return;
        }

        $weeeTaxRegion = $this->getWeeeTaxRegion($basedOn);
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $countryId = $weeeTaxRegion['countryId'];
        $regionId = $weeeTaxRegion['regionId'];

        if (!$countryId && !$regionId) {
            // country and region does not exist
            return;
        } elseif ($countryId && !$regionId) {
            // country exist and region does not exist
            $regionId = 0;
            $exist = $this->weeeTax->isWeeeInLocation($countryId, $regionId, $websiteId);
        } else {
            // country and region exist
            $exist = $this->weeeTax->isWeeeInLocation($countryId, $regionId, $websiteId);
            if (!$exist) {
                // just check the country for weee
                $regionId = 0;
                $exist = $this->weeeTax->isWeeeInLocation($countryId, $regionId, $websiteId);
            }
        }

        if ($exist) {
            $this->httpContext->setValue(
                'weee_tax_region',
                ['countryId' => $countryId, 'regionId' => $regionId],
                0
            );
        }
    }

    /**
     * Get wee tax region.
     *
     * @param string $basedOn
     * @return array
     */
    protected function getWeeeTaxRegion($basedOn)
    {
        $countryId = null;
        $regionId = null;
        $defaultCountryId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            null
        );
        $defaultRegionId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
            ScopeInterface::SCOPE_STORE,
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
        } elseif ($basedOn == 'billing') {
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
