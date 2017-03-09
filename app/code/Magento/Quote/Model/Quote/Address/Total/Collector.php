<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Address Total Collector model
 */
class Collector extends \Magento\Sales\Model\Config\Ordered
{
    /**
     * Path to sort order values of checkout totals
     */
    const XML_PATH_SALES_TOTALS_SORT = 'sales/totals_sort';

    /**
     * Total models array ordered for right display sequence
     *
     * @var array
     */
    protected $_retrievers = [];

    /**
     * Corresponding store object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Config group for totals declaration
     *
     * @var string
     */
    protected $_configGroup = 'totals';

    /**
     * @var string
     */
    protected $_configSection = 'quote';

    /**
     * Cache key for collectors
     *
     * @var string
     */
    protected $_collectorsCacheKey = 'sorted_quote_collectors';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     */
    protected $_totalFactory;

    /**
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory
     * @param mixed $sourceData
     * @param mixed $store
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        $sourceData = null,
        $store = null,
        SerializerInterface $serializer = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_totalFactory = $totalFactory;
        parent::__construct($configCacheType, $logger, $salesConfig, $sourceData, $serializer);
        $this->_store = $store ?: $storeManager->getStore();
        $this->_initModels()->_initCollectors()->_initRetrievers();
    }

    /**
     * Get total models array ordered for right calculation logic
     *
     * @return array
     */
    public function getCollectors()
    {
        return $this->_collectors;
    }

    /**
     * Get total models array ordered for right display sequence
     *
     * @return \Magento\Quote\Model\Quote\Address\Total\AbstractTotal[]
     */
    public function getRetrievers()
    {
        return $this->_retrievers;
    }

    /**
     * Init model class by configuration
     *
     * @param string $class
     * @param string $totalCode
     * @param array $totalConfig
     * @return \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_totalFactory->create($class);
        if (!$model instanceof \Magento\Quote\Model\Quote\Address\Total\AbstractTotal) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The address total model should be extended from
                    \Magento\Quote\Model\Quote\Address\Total\AbstractTotal.'
                )
            );
        }

        $model->setCode($totalCode);
        $this->_modelsConfig[$totalCode] = $this->_prepareConfigArray($totalCode, $totalConfig);
        $this->_modelsConfig[$totalCode] = $model->processConfigArray($this->_modelsConfig[$totalCode], $this->_store);

        return $model;
    }

    /**
     * Initialize retrievers array
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function _initRetrievers()
    {
        $sorts = $this->_scopeConfig->getValue(
            self::XML_PATH_SALES_TOTALS_SORT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_store
        );
        foreach ($sorts as $code => $sortOrder) {
            if (isset($this->_models[$code])) {
                // Reserve enough space for collisions
                $retrieverId = 100 * (int)$sortOrder;
                // Check if there is a retriever with such id and find next available position if needed
                while (isset($this->_retrievers[$retrieverId])) {
                    $retrieverId++;
                }
                $this->_retrievers[$retrieverId] = $this->_models[$code];
            }
        }
        ksort($this->_retrievers);
        $notSorted = array_diff(array_keys($this->_models), array_keys($sorts));
        foreach ($notSorted as $code) {
            $this->_retrievers[] = $this->_models[$code];
        }
        return $this;
    }
}
