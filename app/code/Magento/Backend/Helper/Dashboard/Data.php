<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

use Magento\Framework\App\DeploymentConfig;

/**
 * Data helper for dashboard
 */
class Data extends \Magento\Core\Helper\Data
{
    /**
     * @var \Magento\Framework\Data\Collection\Db
     */
    protected $_stores;

    /**
     * @var string
     */
    protected $_installDate;

    /**
     * Configuration key to installation date
     */
    const INSTALL_DATE = 'install/date';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param DeploymentConfig $deploymentConfig
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        DeploymentConfig $deploymentConfig,
        $dbCompatibleMode = true
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $appState,
            $priceCurrency,
            $dbCompatibleMode
        );
        $this->_installDate = $deploymentConfig->get(self::INSTALL_DATE);
    }

    /**
     * Retrieve stores configured in system.
     *
     * @return \Magento\Framework\Data\Collection\Db
     */
    public function getStores()
    {
        if (!$this->_stores) {
            $this->_stores = $this->_storeManager->getStore()->getResourceCollection()->load();
        }
        return $this->_stores;
    }

    /**
     * Retrieve number of loaded stores
     *
     * @return int
     */
    public function countStores()
    {
        return sizeof($this->_stores->getItems());
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods()
    {
        return [
            '24h' => __('Last 24 Hours'),
            '7d' => __('Last 7 Days'),
            '1m' => __('Current Month'),
            '1y' => __('YTD'),
            '2y' => __('2YTD')
        ];
    }

    /**
     * Create data hash to ensure that we got valid
     * data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     */
    public function getChartDataHash($data)
    {
        $secret = $this->_installDate;
        return md5($data . $secret);
    }
}
