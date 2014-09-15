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
namespace Magento\Log\Model;

/**
 * Log Model
 *
 * @method \Magento\Log\Model\Resource\Log _getResource()
 * @method \Magento\Log\Model\Resource\Log getResource()
 * @method string getSessionId()
 * @method \Magento\Log\Model\Log setSessionId(string $value)
 * @method string getFirstVisitAt()
 * @method \Magento\Log\Model\Log setFirstVisitAt(string $value)
 * @method string getLastVisitAt()
 * @method \Magento\Log\Model\Log setLastVisitAt(string $value)
 * @method int getLastUrlId()
 * @method \Magento\Log\Model\Log setLastUrlId(int $value)
 * @method int getStoreId()
 * @method \Magento\Log\Model\Log setStoreId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    const XML_LOG_CLEAN_DAYS = 'system/log/clean_after_day';

    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Log');
    }

    /**
     * Return log clean time in seconds
     *
     * @return null|string
     */
    public function getLogCleanTime()
    {
        return $this->_scopeConfig->getValue(
            self::XML_LOG_CLEAN_DAYS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) * 60 * 60 * 24;
    }

    /**
     * Clean Logs
     *
     * @return $this
     */
    public function clean()
    {
        $this->getResource()->clean($this);
        return $this;
    }

    /**
     * Return Online Minutes Interval
     *
     * @return int Minutes Interval
     */
    public function getOnlineMinutesInterval()
    {
        $configValue = $this->_scopeConfig->getValue(
            'customer/online_customers/online_minutes_interval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return intval($configValue) > 0 ? intval($configValue) : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }
}
