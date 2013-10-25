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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Address Total Collector model
 */
namespace Magento\Sales\Model\Quote\Address\Total;

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
     * @var \Magento\Core\Model\Store
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
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Sales\Model\Quote\Address\TotalFactory
     */
    protected $_totalFactory;

    /**
     * Init corresponding total models
     *
     * @param \Magento\Core\Model\Cache\Type\Config $configCacheType
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Quote\Address\TotalFactory $totalFactory
     * @param \Magento\Core\Model\Store|null $store
     * @param \Magento\Simplexml\Element|null $sourceData
     */
    public function __construct(
        \Magento\Core\Model\Cache\Type\Config $configCacheType,
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Quote\Address\TotalFactory $totalFactory,
        $store = null,
        $sourceData = null
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
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
     * @throws \Magento\Core\Exception
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_totalFactory->create($class);
        if (!$model instanceof \Magento\Sales\Model\Quote\Address\Total\AbstractTotal) {
            throw new \Magento\Core\Exception(
                __('The address total model should be extended from \Magento\Sales\Model\Quote\Address\Total\AbstractTotal.')
            );
        }

        $model->setCode($totalCode);
        $this->_modelsConfig[$totalCode]= $this->_prepareConfigArray($totalCode, $totalConfig);
        $this->_modelsConfig[$totalCode]= $model->processConfigArray(
            $this->_modelsConfig[$totalCode],
            $this->_store
        );

        return $model;
    }

    /**
     * Initialize retrievers array
     *
     * @return \Magento\Sales\Model\Quote\Address\Total\Collector
     */
    protected function _initRetrievers()
    {
        $sorts = $this->_coreStoreConfig->getConfig(self::XML_PATH_SALES_TOTALS_SORT, $this->_store);
        foreach ($sorts as $code => $sortOrder) {
            if (isset($this->_models[$code])) {
                // Reserve enough space for collisions
                $retrieverId = 100 * (int) $sortOrder;
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
