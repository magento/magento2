<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class Range
{
    const XML_PATH_RANGE_STEP = 'catalog/layered_navigation/price_range_step';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

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
        $this->layer = $layerResolver->get();
    }

    /**
     * @return array
     */
    public function getPriceRange()
    {
        $currentCategory = $this->registry->registry('current_category_filter') ?: $this->layer->getCurrentCategory();

        return $currentCategory->getFilterPriceRange();
    }

    /**
     * @return float
     */
    public function getConfigRangeStep()
    {
        return (double)$this->scopeConfig->getValue(self::XML_PATH_RANGE_STEP, ScopeInterface::SCOPE_STORE);
    }
}
