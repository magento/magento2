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
namespace Magento\Sales\Model\Order\Total\Config;

/**
 * Configuration class for totals
 */
class Base extends \Magento\Sales\Model\Config\Ordered
{
    /**
     * Cache key for collectors
     *
     * @var string
     */
    protected $_collectorsCacheKey = 'sorted_collectors';

    /**
     * Total models list
     *
     * @var array
     */
    protected $_totalModels = array();

    /**
     * Configuration path where to collect registered totals
     *
     * @var string
     */
    protected $_configGroup = 'totals';

    /**
     * @var \Magento\Sales\Model\Order\TotalFactory
     */
    protected $_orderTotalFactory;

    /**
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory
     * @param mixed $sourceData
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Logger $logger,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory,
        $sourceData = null
    ) {
        parent::__construct($configCacheType, $logger, $salesConfig, $sourceData);
        $this->_orderTotalFactory = $orderTotalFactory;
    }

    /**
     * Init model class by configuration
     *
     * @param string $class
     * @param string $totalCode
     * @param array $totalConfig
     * @return \Magento\Sales\Model\Order\Total\AbstractTotal
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_orderTotalFactory->create($class);
        if (!$model instanceof \Magento\Sales\Model\Order\Total\AbstractTotal) {
            throw new \Magento\Framework\Model\Exception(
                __('The total model should be extended from \Magento\Sales\Model\Order\Total\AbstractTotal.')
            );
        }

        $model->setCode($totalCode);
        $model->setTotalConfigNode($totalConfig);
        $this->_modelsConfig[$totalCode] = $this->_prepareConfigArray($totalCode, $totalConfig);
        $this->_modelsConfig[$totalCode] = $model->processConfigArray($this->_modelsConfig[$totalCode]);
        return $model;
    }

    /**
     * Retrieve total calculation models
     *
     * @return array
     */
    public function getTotalModels()
    {
        if (empty($this->_totalModels)) {
            $this->_initModels();
            $this->_initCollectors();
            $this->_totalModels = $this->_collectors;
        }
        return $this->_totalModels;
    }
}
