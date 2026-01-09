<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Proxy that delegates execution to an original cache type instance, if access is allowed at the moment.
 * It's typical for "access proxies" to have a decorator-like implementation, the difference is logical -
 * controlling access rather than attaching additional responsibility to a subject.
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\Cache\CacheConstants;

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
     * @inheritDoc
     */
    public function test($identifier)
    {
        if (!$this->_isEnabled()) {
            return false;
        }
        return parent::test($identifier);
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        if (!$this->_isEnabled()) {
            return false;
        }
        return parent::load($identifier);
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::remove($identifier);
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        if (!$this->_isEnabled()) {
            return true;
        }
        return parent::clean($mode, $tags);
    }
}
