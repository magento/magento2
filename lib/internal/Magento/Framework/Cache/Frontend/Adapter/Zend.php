<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Factory that creates the \Zend_Cache_Cores
     *
     * @var \Closure
     */
    private $frontendFactory;

    /**
     * The pid that owns the $_frontend object
     *
     * @var int
     */
    private $pid;

    /**
     * We need to keep references to parent's frontends so that they don't get destroyed
     *
     * @var array
     */
    private $parentFrontends = [];

    /**
     * @param \Closure $frontendFactory
     */
    public function __construct(\Closure $frontendFactory)
    {
        $this->frontendFactory = $frontendFactory;
        $this->_frontend = $frontendFactory();
        $this->pid = getmypid();
    }

    /**
     * @inheritdoc
     */
    public function test($identifier)
    {
        return $this->getFrontEnd()->test($this->_unifyId($identifier));
    }

    /**
     * @inheritdoc
     */
    public function load($identifier)
    {
        return $this->getFrontEnd()->load($this->_unifyId($identifier));
    }

    /**
     * @inheritdoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->getFrontEnd()->save($data, $this->_unifyId($identifier), $this->_unifyIds($tags), $lifeTime);
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        return $this->getFrontEnd()->remove($this->_unifyId($identifier));
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException Exception is thrown when non-supported cleaning mode is specified
     * @throws \Zend_Cache_Exception
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
        return $this->getFrontEnd()->clean($mode, $this->_unifyIds($tags));
    }

    /**
     * @inheritdoc
     */
    public function getBackend()
    {
        return $this->getFrontEnd()->getBackend();
    }

    /**
     * @inheritdoc
     */
    public function getLowLevelFrontend()
    {
        return $this->getFrontEnd();
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

    /**
     * Get frontEnd cache adapter for current pid
     *
     * @return \Zend_Cache_Core
     */
    private function getFrontEnd()
    {
        if (getmypid() === $this->pid) {
            return $this->_frontend;
        }
        // Note: We hide the parent process's _frontend so that the destructor won't get called on it.
        // If the destructor were called, then the parent process's connection would be disconnected.
        $this->parentFrontends[] = $this->_frontend;
        $frontendFactory = $this->frontendFactory;
        $this->_frontend = $frontendFactory();
        $this->pid = getmypid();
        return $this->_frontend;
    }
}
