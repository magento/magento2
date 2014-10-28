<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Quote\Address\Total;

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
    protected $_retrievers = array();

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
     * @var \Magento\Sales\Model\Quote\Address\TotalFactory
     */
    protected $_totalFactory;

    /**
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Quote\Address\TotalFactory $totalFactory
     * @param mixed $sourceData
     * @param mixed $store
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Logger $logger,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Quote\Address\TotalFactory $totalFactory,
        $sourceData = null,
        $store = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_totalFactory = $totalFactory;
        parent::__construct($configCacheType, $logger, $salesConfig, $sourceData);
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
     * @return array
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
     * @return \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_totalFactory->create($class);
        if (!$model instanceof \Magento\Sales\Model\Quote\Address\Total\AbstractTotal) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    'The address total model should be extended from \Magento\Sales\Model\Quote\Address\Total\AbstractTotal.'
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
     */
    private function _initRetrievers()
    {
        $sorts = $this->_scopeConfig->getValue(self::XML_PATH_SALES_TOTALS_SORT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_store);
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
