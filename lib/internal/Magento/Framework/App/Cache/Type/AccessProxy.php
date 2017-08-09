<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Proxy that delegates execution to an original cache type instance, if access is allowed at the moment.
 * It's typical for "access proxies" to have a decorator-like implementation, the difference is logical -
 * controlling access rather than attaching additional responsibility to a subject.
 */
namespace Magento\Framework\App\Cache\Type;

/**
 * Class \Magento\Framework\App\Cache\Type\AccessProxy
 *
 */
class AccessProxy extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * Cache types manager
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $_cacheState;

    /**
     * Cache type identifier
     *
     * @var string
     */
    private $_identifier;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param string $identifier Cache type identifier
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $frontend,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        $identifier
    ) {
        parent::__construct($frontend);
        $this->_cacheState = $cacheState;
        $this->_identifier = $identifier;
    }

    /**
     * Whether a cache type is enabled at the moment or not
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return $this->_cacheState->isEnabled($this->_identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        if (!$this->_isEnabled()) {
            return false;
        }
        return parent::test($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        if (!$this->_isEnabled()) {
            return false;
        }
        return parent::load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::remove($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::clean($mode, $tags);
    }
}
