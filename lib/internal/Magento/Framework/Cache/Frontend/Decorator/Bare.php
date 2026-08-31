<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Cache frontend decorator that attaches no additional responsibility to a decorated instance.
 * To be used as an ancestor for concrete decorators to conveniently override only methods of interest.
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\CacheConstants;

class Bare implements \Magento\Framework\Cache\FrontendInterface
{
    /**
     * Cache frontend instance to delegate actual cache operations to
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_frontend;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $frontend)
    {
        $this->_frontend = $frontend;
    }

    /**
     * Set frontend
     *
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @return $this
     */
    protected function setFrontend(\Magento\Framework\Cache\FrontendInterface $frontend)
    {
        $this->_frontend = $frontend;
        return $this;
    }

    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    protected function _getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * @inheritdoc
     */
    public function test($identifier)
    {
        return $this->_getFrontend()->test($identifier);
    }

    /**
     * @inheritdoc
     */
    public function load($identifier)
    {
        return $this->_getFrontend()->load($identifier);
    }

    /**
     * Batched multi-load passthrough (used by the preloading wrapper). Delegates to the wrapped
     * frontend's loadMultiple() when it has one; otherwise falls back to per-key loads so the chain
     * still works for non-batching backends.
     *
     * @param string[] $identifiers
     * @return array<string, mixed>
     */
    public function loadMultiple(array $identifiers): array
    {
        $frontend = $this->_getFrontend();
        if (method_exists($frontend, 'loadMultiple')) {
            return $frontend->loadMultiple($identifiers);
        }
        $out = [];
        foreach ($identifiers as $id) {
            $value = $frontend->load($id);
            if ($value !== false) {
                $out[$id] = $value;
            }
        }
        return $out;
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->_getFrontend()->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        return $this->_getFrontend()->remove($identifier);
    }

    /**
     * @inheritdoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        return $this->_getFrontend()->clean($mode, $tags);
    }

    /**
     * @inheritdoc
     */
    public function getBackend()
    {
        return $this->_getFrontend()->getBackend();
    }

    /**
     * @inheritdoc
     */
    public function getLowLevelFrontend()
    {
        return $this->_getFrontend()->getLowLevelFrontend();
    }

    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}
