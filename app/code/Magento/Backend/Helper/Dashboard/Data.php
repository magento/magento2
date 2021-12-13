<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Data helper for dashboard
 *
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
    /**
     * @var AbstractDb
     */
    protected $_stores;

    /**
     * @var string
     */
    protected $_installDate;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Period
     */
    private $period;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DeploymentConfig $deploymentConfig
     * @param Period|null $period
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig,
        ?Period $period = null
    ) {
        parent::__construct($context);
        $this->_installDate = $deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE);
        $this->_storeManager = $storeManager;
        $this->period = $period ?? ObjectManager::getInstance()->get(Period::class);
    }

    /**
     * Retrieve stores configured in system.
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
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
        return count($this->_stores->getItems());
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @deprecated 102.0.0 periods were moved to it's own class
     * @see Period::getDatePeriods()
     *
     * @return array
     */
    public function getDatePeriods()
    {
        return $this->period->getDatePeriods();
    }

    /**
     * Create data hash to ensure that we got valid data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     */
    public function getChartDataHash($data)
    {
        $secret = $this->_installDate;
        // phpcs:disable Magento2.Security.InsecureFunction.FoundWithAlternative
        return md5($data . $secret);
    }
}
