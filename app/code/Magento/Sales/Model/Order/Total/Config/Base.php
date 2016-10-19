<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Total\Config;

use Magento\Framework\Serialize\SerializerInterface;

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
    protected $_totalModels = [];

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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory
     * @param mixed $sourceData
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory,
        $sourceData = null,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($configCacheType, $logger, $salesConfig, $sourceData, $serializer);
        $this->_orderTotalFactory = $orderTotalFactory;
    }

    /**
     * Init model class by configuration
     *
     * @param string $class
     * @param string $totalCode
     * @param array $totalConfig
     * @return \Magento\Sales\Model\Order\Total\AbstractTotal
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_orderTotalFactory->create($class);
        if (!$model instanceof \Magento\Sales\Model\Order\Total\AbstractTotal) {
            throw new \Magento\Framework\Exception\LocalizedException(
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
