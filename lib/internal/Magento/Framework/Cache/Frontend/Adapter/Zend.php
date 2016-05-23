<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Frontend\Adapter;

/**
 * Adapter for Magento -> Zend cache frontend interfaces
 */
class Zend implements \Magento\Framework\Cache\FrontendInterface
{
    /**
     * @var \Zend_Cache_Core
     */
    protected $_frontend;

    /**
     * @param \Zend_Cache_Core $frontend
     */
    public function __construct(\Zend_Cache_Core $frontend)
    {
        $this->_frontend = $frontend;
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        return $this->_frontend->test($this->_unifyId($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return $this->_frontend->load($this->_unifyId($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->_frontend->save($data, $this->_unifyId($identifier), $this->_unifyIds($tags), $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return $this->_frontend->remove($this->_unifyId($identifier));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException Exception is thrown when non-supported cleaning mode is specified
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        // Cleaning modes 'old' and 'notMatchingTag' are prohibited as a trade off for decoration reliability
        if (!in_array(
            $mode,
            [
                \Zend_Cache::CLEANING_MODE_ALL,
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG
            ]
        )
        ) {
            throw new \InvalidArgumentException(
                "Magento cache frontend does not support the cleaning mode '{$mode}'."
            );
        }
        return $this->_frontend->clean($mode, $this->_unifyIds($tags));
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend()
    {
        return $this->_frontend->getBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->_frontend;
    }

    /**
     * Retrieve single unified identifier
     *
     * @param string $identifier
     * @return string
     */
    protected function _unifyId($identifier)
    {
        return strtoupper($identifier);
    }

    /**
     * Retrieve multiple unified identifiers
     *
     * @param array $ids
     * @return array
     */
    protected function _unifyIds(array $ids)
    {
        foreach ($ids as $key => $value) {
            $ids[$key] = $this->_unifyId($value);
        }
        return $ids;
    }
}
