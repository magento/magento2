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

namespace Magento\TestFramework\Interception;

class PluginList extends \Magento\Interception\PluginList\PluginList
{
    /**
     * @var array
     */
    protected $_originScopeScheme = array();

    /**
     * @param \Magento\Config\ReaderInterface $reader
     * @param \Magento\Config\ScopeInterface $configScope
     * @param \Magento\Config\CacheInterface $cache
     * @param \Magento\ObjectManager\Relations $relations
     * @param \Magento\ObjectManager\Config $omConfig
     * @param \Magento\Interception\Definition $definitions
     * @param array $scopePriorityScheme
     * @param \Magento\ObjectManager\Definition\Compiled $classDefinitions
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Config\ReaderInterface $reader,
        \Magento\Config\ScopeInterface $configScope,
        \Magento\Config\CacheInterface $cache,
        \Magento\ObjectManager\Relations $relations,
        \Magento\ObjectManager\Config $omConfig,
        \Magento\Interception\Definition $definitions,
        array $scopePriorityScheme,
        $cacheId = 'plugins',
        \Magento\ObjectManager\Definition\Compiled $classDefinitions = null
    ) {
        parent::__construct($reader, $configScope, $cache, $relations, $omConfig,
            $definitions, $scopePriorityScheme, $cacheId, $classDefinitions);
        $this->_originScopeScheme = $this->_scopePriorityScheme;
    }

    /**
     * Reset internal cache
     */
    public function reset()
    {
        $this->_scopePriorityScheme = $this->_originScopeScheme;
        $this->_data = array();
        $this->_loadedScopes = array();
    }
} 