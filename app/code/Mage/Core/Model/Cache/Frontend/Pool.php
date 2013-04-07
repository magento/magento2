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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * In-memory readonly pool of cache front-end instances, specified in the configuration
 */
class Mage_Core_Model_Cache_Frontend_Pool implements Iterator
{
    /**#@+
     * XPaths where cache frontend settings reside
     */
    const XML_PATH_SETTINGS_DEFAULT = 'global/cache';
    const XML_PATH_SETTINGS_CUSTOM  = 'global/cache_advanced';
    /**#@-*/

    /**
     * Frontend identifier associated with the default settings
     */
    const DEFAULT_FRONTEND_ID = 'generic';

    /**
     * @var Mage_Core_Model_ConfigInterface
     */
    private $_config;

    /**
     * @var Mage_Core_Model_Cache_Frontend_Factory
     */
    private $_factory;

    /**
     * @var Magento_Cache_FrontendInterface[]
     */
    private $_instances;

    /**
     * Store references to objects, necessary to perform delayed initialization
     *
     * @param Mage_Core_Model_Config_Primary $cacheConfig
     * @param Mage_Core_Model_Cache_Frontend_Factory $frontendFactory
     */
    public function __construct(
        Mage_Core_Model_Config_Primary $cacheConfig,
        Mage_Core_Model_Cache_Frontend_Factory $frontendFactory
    ) {
        $this->_config = $cacheConfig;
        $this->_factory = $frontendFactory;
    }

    /**
     * Load frontend instances from the configuration, to be used for delayed initialization
     */
    protected function _initialize()
    {
        if ($this->_instances === null) {
            $this->_instances = array();
            // default front-end
            $frontendNode = $this->_config->getNode(self::XML_PATH_SETTINGS_DEFAULT);
            $frontendOptions = $frontendNode ? $frontendNode->asArray() : array();
            $this->_instances[self::DEFAULT_FRONTEND_ID] = $this->_factory->create($frontendOptions);
            // additional front-ends
            $frontendNodes = $this->_config->getNode(self::XML_PATH_SETTINGS_CUSTOM);
            if ($frontendNodes) {
                /** @var $frontendNode Varien_Simplexml_Element */
                foreach ($frontendNodes->children() as $frontendNode) {
                    $frontendId = $frontendNode->getName();
                    $frontendOptions = $frontendNode->asArray();
                    $this->_instances[$frontendId] = $this->_factory->create($frontendOptions);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Magento_Cache_FrontendInterface
     */
    public function current()
    {
        $this->_initialize();
        return current($this->_instances);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->_initialize();
        return key($this->_instances);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_initialize();
        next($this->_instances);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_initialize();
        reset($this->_instances);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->_initialize();
        return (bool)current($this->_instances);
    }

    /**
     * Retrieve frontend instance by its unique identifier, or return NULL, if identifier is not recognized
     *
     * @param string $identifier Cache frontend identifier
     * @return Magento_Cache_FrontendInterface Cache frontend instance
     */
    public function get($identifier)
    {
        $this->_initialize();
        if (isset($this->_instances[$identifier])) {
            return $this->_instances[$identifier];
        }
        return null;
    }
}
