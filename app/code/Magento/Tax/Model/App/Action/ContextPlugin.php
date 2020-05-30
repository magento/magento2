<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\App\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;

/**
 * Provides Action Context on before executing Controller Action
 */
class ContextPlugin
{
    /**
     * @var Session
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
     * @var Calculation
     */
    protected $taxCalculation;

    /**
     * Module manager
     *
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Cache config
     *
     * @var PageCacheConfig
     */
    private $cacheConfig;

    /**
     * @param Session $customerSession
     * @param HttpContext $httpContext
     * @param Calculation $calculation
     * @param TaxHelper $taxHelper
     * @param ModuleManager $moduleManager
     * @param PageCacheConfig $cacheConfig
     */
    public function __construct(
        Session $customerSession,
        HttpContext $httpContext,
        Calculation $calculation, //phpcs:ignore Magento2.Classes.DiscouragedDependencies
        TaxHelper $taxHelper,
        ModuleManager $moduleManager,
        PageCacheConfig $cacheConfig
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->taxCalculation = $calculation;
        $this->taxHelper = $taxHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Before dispatch.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if (!$this->customerSession->isLoggedIn() ||
            !$this->moduleManager->isEnabled('Magento_PageCache') ||
            !$this->cacheConfig->isEnabled() ||
            !$this->taxHelper->isCatalogPriceDisplayAffectedByTax()) {
            return;
        }

        $defaultBillingAddress = $this->customerSession->getDefaultTaxBillingAddress();
        $defaultShippingAddress = $this->customerSession->getDefaultTaxShippingAddress();
        $customerTaxClassId = $this->customerSession->getCustomerTaxClassId();

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
}
