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
namespace Magento\Framework\App\Cache\Frontend;

/**
 * In-memory readonly pool of all cache front-end instances known to the system
 */
class Pool implements \Iterator
{
    /**
     * Frontend identifier associated with the default settings
     */
    const DEFAULT_FRONTEND_ID = 'default';

    /**
     * @var \Magento\Framework\App\Arguments
     */
    private $_arguments;

    /**
     * @var Factory
     */
    private $_factory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances;

    /**
     * @var array
     */
    private $_frontendSettings;

    /**
     * @param \Magento\Framework\App\Arguments $arguments
     * @param Factory $frontendFactory
     * @param array $frontendSettings Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    public function __construct(
        \Magento\Framework\App\Arguments $arguments,
        Factory $frontendFactory,
        array $frontendSettings = array()
    ) {
        $this->_arguments = $arguments;
        $this->_factory = $frontendFactory;
        $this->_frontendSettings = $frontendSettings + array(self::DEFAULT_FRONTEND_ID => array());
    }

    /**
     * Create instances of every cache frontend known to the system.
     * Method is to be used for delayed initialization of the iterator.
     *
     * @return void
     */
    protected function _initialize()
    {
        if ($this->_instances === null) {
            $this->_instances = array();
            foreach ($this->_getCacheSettings() as $frontendId => $frontendOptions) {
                $this->_instances[$frontendId] = $this->_factory->create($frontendOptions);
            }
        }
    }

    /**
     * Retrieve settings for all cache front-ends known to the system
     *
     * @return array Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    protected function _getCacheSettings()
    {
        /*
         * Merging is intentionally implemented through array_merge() instead of array_replace_recursive()
         * to avoid "inheritance" of the default settings that become irrelevant as soon as cache storage type changes
         */
        return array_merge($this->_frontendSettings, $this->_arguments->getCacheFrontendSettings());
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Cache\FrontendInterface
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
     * Retrieve frontend instance by its unique identifier
     *
     * @param string $identifier Cache frontend identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     * @throws \InvalidArgumentException
     */
    public function get($identifier)
    {
        $this->_initialize();
        if (isset($this->_instances[$identifier])) {
            return $this->_instances[$identifier];
        }
        throw new \InvalidArgumentException("Cache frontend '{$identifier}' is not recognized.");
    }
}
