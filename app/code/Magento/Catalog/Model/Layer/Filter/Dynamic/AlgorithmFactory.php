<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class AlgorithmFactory
{
    /**
     * XML configuration path for Price Layered Navigation
     */
    const XML_PATH_RANGE_CALCULATION = 'catalog/layered_navigation/price_range_calculation';

    const RANGE_CALCULATION_AUTO = 'auto';
    const RANGE_CALCULATION_IMPROVED = 'improved';
    const RANGE_CALCULATION_MANUAL = 'manual';

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $algorithms;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $algorithms
     */
    public function __construct(ObjectManagerInterface $objectManager, ScopeConfigInterface $scopeConfig, array $algorithms)
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->algorithms = $algorithms;
    }

    /**
     * Create algorithm
     *
     * @param array $data
     * @return AlgorithmInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $data = [])
    {
        $calculationType = $this->scopeConfig->getValue(self::XML_PATH_RANGE_CALCULATION, ScopeInterface::SCOPE_STORE);

        if (!isset($this->algorithms[$calculationType])) {
            throw new LocalizedException(__('%1 was not found in algorithms', $calculationType));
        }

        $className = $this->algorithms[$calculationType];
        $model = $this->objectManager->create($className, $data);

        if (!$model instanceof AlgorithmInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmInterface', $className)
            );
        }

        return $model;
    }
}
