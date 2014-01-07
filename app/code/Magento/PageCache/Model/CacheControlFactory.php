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
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Factory class for cache control interface
 */
namespace Magento\PageCache\Model;

class CacheControlFactory
{
    /**
     * Paths to external cache config option
     */
    const XML_PATH_EXTERNAL_CACHE_CONTROL  = 'system/external_page_cache/control';

    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var array
     */
    protected $_cacheControls;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param array $cacheControls
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        array $cacheControls = array()
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeConfig = $storeConfig;
        $this->_cacheControls = $cacheControls;
    }

    /**
     * Return all available external cache controls
     *
     * @return array
     */
    public function getCacheControls()
    {
        return $this->_cacheControls;
    }

    /**
     * Initialize proper external cache control model
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\PageCache\Model\Control\ControlInterface
     */
    public function getCacheControlInstance()
    {
        $usedControl = $this->_storeConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_CONTROL);
        if ($usedControl) {
            foreach ($this->getCacheControls() as $control => $info) {
                if ($control == $usedControl && !empty($info['instance'])) {
                    return $this->_objectManager->get($info['instance']);
                }
            }
        }
        throw new \Magento\Core\Exception(__('Failed to load external cache control'));
    }
}
