<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 100.0.2
 */
class Range
{
    public const XML_PATH_RANGE_STEP = 'catalog/layered_navigation/price_range_step';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param Resolver $layerResolver
     * @internal param \Magento\Framework\Registry $registry
     */
    public function __construct(Registry $registry, ScopeConfigInterface $scopeConfig, Resolver $layerResolver)
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Get price range from the current category.
     *
     * @return array
     */
    public function getPriceRange()
    {
        $currentCategory = $this->registry->registry('current_category_filter')
            ?: $this->layerResolver->get()->getCurrentCategory();

        return $currentCategory->getFilterPriceRange();
    }

    /**
     * Get configured layered navigation price range step.
     *
     * @return float
     */
    public function getConfigRangeStep()
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_RANGE_STEP, ScopeInterface::SCOPE_STORE);
    }
}
