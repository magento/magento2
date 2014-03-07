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
 * @package     Magento_CacheInvalidate
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CacheInvalidate\Model;

/**
 * Class Observer
 */
class Observer
{
    /**
     * Application config object
     *
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\CacheInvalidate\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\HTTP\Adapter\Curl
     */
    protected $_curlAdapter;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\HTTP\Adapter\Curl $curlAdapter
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\PageCache\Helper\Data $helper,
        \Magento\HTTP\Adapter\Curl $curlAdapter
    ){
        $this->_config = $config;
        $this->_helper = $helper;
        $this->_curlAdapter = $curlAdapter;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Event\Observer $observer
     */
    public function invalidateVarnish(\Magento\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        if($object instanceof \Magento\Object\IdentityInterface) {
            if($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH) {
                $this->sendPurgeRequest(implode('|', $object->getIdentities()));
            }
        }
    }

    /**
     * Flash Varnish cache
     *
     * @param \Magento\Event\Observer $observer
     */
    public function flushAllCache(\Magento\Event\Observer $observer)
    {
        if($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH) {
            $this->sendPurgeRequest('.*');
        }
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     */
    protected function sendPurgeRequest($tagsPattern)
    {
        $headers = array("X-Magento-Tags-Pattern: {$tagsPattern}");
        $this->_curlAdapter->setOptions(array(CURLOPT_CUSTOMREQUEST => 'PURGE'));
        $this->_curlAdapter->write('', $this->_helper->getUrl('*'), '1.1', $headers);
        $this->_curlAdapter->read();
        $this->_curlAdapter->close();
    }
}
