<?php
/**
 * An ultimate accessor to cache types' statuses
 *
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
namespace Magento\Framework\App\Cache;

class State implements StateInterface
{
    /**
     * Cache identifier used to store cache type statuses
     */
    const CACHE_ID  = 'core_cache_options';
    
    /**
     * Disallow cache
     */
    const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Persistent storage of cache type statuses
     *
     * @var State\OptionsInterface
     */
    private $_options;

    /**
     * Cache frontend to delegate actual cache operations to
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cacheFrontend;

    /**
     * Associative array of cache type codes and their statuses (enabled/disabled)
     *
     * @var array
     */
    private $_typeStatuses = array();

    /**
     * @param State\OptionsInterface $options
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param bool $banAll Whether all cache types are forced to be disabled
     */
    public function __construct(
        State\OptionsInterface $options,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        $banAll = false
    ) {
        $this->_options = $options;
        $this->_cacheFrontend =
            $cacheFrontendPool->get(\Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID);
        $this->_loadTypeStatuses($banAll);
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @param bool $forceDisableAll
     * @return void
     */
    private function _loadTypeStatuses($forceDisableAll = false)
    {
        $typeOptions = $this->_cacheFrontend->load(self::CACHE_ID);
        if ($typeOptions !== false) {
            $typeOptions = unserialize($typeOptions);
        } else {
            $typeOptions = $this->_options->getAllOptions();
            if ($typeOptions !== false) {
                $this->_cacheFrontend->save(serialize($typeOptions), self::CACHE_ID);
            }
        }
        if ($typeOptions) {
            foreach ($typeOptions as $cacheType => $isTypeEnabled) {
                $this->setEnabled($cacheType, $isTypeEnabled && !$forceDisableAll);
            }
        }
    }

    /**
     * Whether a cache type is enabled or not at the moment
     *
     * @param string $cacheType
     * @return bool
     */
    public function isEnabled($cacheType)
    {
        return isset($this->_typeStatuses[$cacheType]) ? (bool)$this->_typeStatuses[$cacheType] : false;
    }

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function setEnabled($cacheType, $isEnabled)
    {
        $this->_typeStatuses[$cacheType] = (int)$isEnabled;
    }

    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     */
    public function persist()
    {
        $this->_options->saveAllOptions($this->_typeStatuses);
        $this->_cacheFrontend->remove(self::CACHE_ID);
    }
}
