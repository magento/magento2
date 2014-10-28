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
namespace Magento\Log\Model\Visitor;

/**
 * Prepare Log Online Visitors Model
 *
 * @method \Magento\Log\Model\Resource\Visitor\Online getResource()
 * @method string getVisitorType()
 * @method \Magento\Log\Model\Visitor\Online setVisitorType(string $value)
 * @method int getRemoteAddr()
 * @method \Magento\Log\Model\Visitor\Online setRemoteAddr(int $value)
 * @method string getFirstVisitAt()
 * @method \Magento\Log\Model\Visitor\Online setFirstVisitAt(string $value)
 * @method string getLastVisitAt()
 * @method \Magento\Log\Model\Visitor\Online setLastVisitAt(string $value)
 * @method int getCustomerId()
 * @method \Magento\Log\Model\Visitor\Online setCustomerId(int $value)
 * @method string getLastUrl()
 * @method \Magento\Log\Model\Visitor\Online setLastUrl(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Online extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_ONLINE_INTERVAL = 'customer/online_customers/online_minutes_interval';

    const XML_PATH_UPDATE_FREQUENCY = 'log/visitor/online_update_frequency';

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
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor\Online');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Log\Model\Resource\Visitor\Online
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Prepare Online visitors collection
     *
     * @return $this
     */
    public function prepare()
    {
        $this->_getResource()->prepare($this);
        return $this;
    }

    /**
     * Retrieve last prepare at timestamp
     *
     * @return int
     */
    public function getPrepareAt()
    {
        return $this->_cacheManager->load('log_visitor_online_prepare_at');
    }

    /**
     * Set Prepare at timestamp (if time is null, set current timestamp)
     *
     * @param int $time
     * @return $this
     */
    public function setPrepareAt($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        $this->_cacheManager->save($time, 'log_visitor_online_prepare_at');
        return $this;
    }

    /**
     * Retrieve data update Frequency in second
     *
     * @return int
     */
    public function getUpdateFrequency()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_UPDATE_FREQUENCY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Online Interval (in minutes)
     *
     * @return int
     */
    public function getOnlineInterval()
    {
        $value = intval(
            $this->_scopeConfig->getValue(
                self::XML_PATH_ONLINE_INTERVAL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        if (!$value) {
            $value = \Magento\Log\Model\Log::DEFAULT_ONLINE_MINUTES_INTERVAL;
        }
        return $value;
    }
}
