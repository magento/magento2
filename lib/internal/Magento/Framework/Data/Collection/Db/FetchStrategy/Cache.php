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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Retrieving collection data from cache, failing over to another fetch strategy, if cache not yet exists
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

class Cache implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cache;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $_fetchStrategy;

    /**
     * @var string
     */
    protected $_cacheIdPrefix = '';

    /**
     * @var array
     */
    protected $_cacheTags = array();

    /**
     * @var int|bool|null
     */
    protected $_cacheLifetime = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param string $cacheIdPrefix
     * @param array $cacheTags
     * @param int|bool|null $cacheLifetime
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        $cacheIdPrefix = '',
        array $cacheTags = array(),
        $cacheLifetime = null
    ) {
        $this->_cache = $cache;
        $this->_fetchStrategy = $fetchStrategy;
        $this->_cacheIdPrefix = $cacheIdPrefix;
        $this->_cacheTags = $cacheTags;
        $this->_cacheLifetime = $cacheLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(\Zend_Db_Select $select, array $bindParams = array())
    {
        $cacheId = $this->_getSelectCacheId($select);
        $result = $this->_cache->load($cacheId);
        if ($result) {
            $result = unserialize($result);
        } else {
            $result = $this->_fetchStrategy->fetchAll($select, $bindParams);
            $this->_cache->save(serialize($result), $cacheId, $this->_cacheTags, $this->_cacheLifetime);
        }
        return $result;
    }

    /**
     * Determine cache identifier based on select query
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    protected function _getSelectCacheId($select)
    {
        return $this->_cacheIdPrefix . md5((string)$select);
    }
}
