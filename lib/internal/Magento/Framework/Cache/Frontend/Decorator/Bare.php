<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cache frontend decorator that attaches no additional responsibility to a decorated instance.
 * To be used as an ancestor for concrete decorators to conveniently override only methods of interest.
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

/**
 * Class \Magento\Framework\Cache\Frontend\Decorator\Bare
 *
 * @since 2.0.0
 */
class Bare implements \Magento\Framework\Cache\FrontendInterface
{
    /**
     * Cache frontend instance to delegate actual cache operations to
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     * @since 2.0.0
     */
    private $_frontend;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function test($identifier)
    {
        return $this->_getFrontend()->test($identifier);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function load($identifier)
    {
        return $this->_getFrontend()->load($identifier);
    }

    /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->_getFrontend()->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function remove($identifier)
    {
        return $this->_getFrontend()->remove($identifier);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        return $this->_getFrontend()->clean($mode, $tags);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBackend()
    {
        return $this->_getFrontend()->getBackend();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLowLevelFrontend()
    {
        return $this->_getFrontend()->getLowLevelFrontend();
    }
}
