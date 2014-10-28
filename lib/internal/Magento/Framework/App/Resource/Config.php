<?php
/**
 * Resource configuration. Uses application configuration to retrieve resource connection information.
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
namespace Magento\Framework\App\Resource;

class Config extends \Magento\Framework\Config\Data\Scoped implements ConfigInterface
{
    const DEFAULT_SETUP_CONNECTION = 'default';

    const PARAM_INITIAL_RESOURCES = 'resource';

    /**
     * List of connection names per resource
     *
     * @var array
     */
    protected $_connectionNames = array();

    /**
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param array $initialResources
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'resourcesCache',
        $initialResources = array()
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);
        foreach ($initialResources as $resourceName => $resourceData) {
            if (!isset($resourceData['connection'])) {
                throw new \InvalidArgumentException('Invalid initial resource configuration');
            }
            $this->_connectionNames[$resourceName] = $resourceData['connection'];
        }
    }

    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     */
    public function getConnectionName($resourceName)
    {
        $connectionName = self::DEFAULT_SETUP_CONNECTION;

        if (!isset($this->_connectionNames[$resourceName])) {

            $resourcesConfig = $this->get();
            $pointerResourceName = $resourceName;
            while (true) {
                if (isset($resourcesConfig[$pointerResourceName]['connection'])) {
                    $connectionName = $resourcesConfig[$pointerResourceName]['connection'];
                    $this->_connectionNames[$resourceName] = $connectionName;
                    break;
                } elseif (isset($this->_connectionNames[$pointerResourceName])) {
                    $this->_connectionNames[$resourceName] = $this->_connectionNames[$pointerResourceName];
                    break;
                } elseif (isset($resourcesConfig[$pointerResourceName]['extends'])) {
                    $pointerResourceName = $resourcesConfig[$pointerResourceName]['extends'];
                } else {
                    break;
                }
            }
        } else {
            $connectionName = $this->_connectionNames[$resourceName];
        }

        return $connectionName;
    }
}
