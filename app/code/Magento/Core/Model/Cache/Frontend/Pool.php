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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * In-memory readonly pool of cache front-end instances, specified in the configuration
 */
namespace Magento\Core\Model\Cache\Frontend;

class Pool implements \Iterator
{
    /**
     * Frontend identifier associated with the default settings
     */
    const DEFAULT_FRONTEND_ID = 'generic';

    /**
     * @var \Magento\Core\Model\Cache\Frontend\Factory
     */
    private $_factory;

    /**
     * @var \Magento\Cache\FrontendInterface[]
     */
    private $_instances;

    /**
     * Advanced config settings
     *
     * @var array
     */
    private $_advancedSettings;

    /**
     * Default cache settings
     *
     * @var array
     */
    private $_defaultSettings;

    /**
     * @param \Magento\Core\Model\Cache\Frontend\Factory $frontendFactory
     * @param array $defaultSettings
     * @param array $advancedSettings
     */
    public function __construct(
        \Magento\Core\Model\Cache\Frontend\Factory $frontendFactory,
        array $defaultSettings = array(),
        array $advancedSettings = array()
    ) {
        $this->_factory = $frontendFactory;
        $this->_advancedSettings = $advancedSettings;
        $this->_defaultSettings = empty($defaultSettings) == false ? $defaultSettings : array();
    }

    /**
     * Load frontend instances from the configuration, to be used for delayed initialization
     */
    protected function _initialize()
    {
        if ($this->_instances === null) {
            $this->_instances = array();
            // default front-end
            $this->_instances[self::DEFAULT_FRONTEND_ID] = $this->_factory->create($this->_defaultSettings);
            // additional front-ends

            if ($this->_advancedSettings) {
                /** @var $frontendNode \Magento\Simplexml\Element */
                foreach ($this->_advancedSettings as  $frontendId => $frontendOptions) {
                    $this->_instances[$frontendId] = $this->_factory->create($frontendOptions);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Cache\FrontendInterface
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
     * @return \Magento\Cache\FrontendInterface Cache frontend instance
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
