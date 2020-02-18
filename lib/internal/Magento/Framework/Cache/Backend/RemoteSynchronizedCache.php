<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Backend;

/**
 * Remote synchronized cache
 *
 * This class created for correct work local caches with multiple web nodes,
 * that will be check cache status from remote cache
 */
class RemoteSynchronizedCache extends \Zend_Cache_Backend implements \Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Local backend cache adapter
     *
     * @var \Zend_Cache_Backend_ExtendedInterface
     */
    private $local;

    /**
     * Remote backend cache adapter
     *
     * @var \Zend_Cache_Backend_ExtendedInterface
     */
    private $remote;

    /**
     * Cache invalidation time
     *
     * @var \Zend_Cache_Backend_ExtendedInterface
     */
    protected $cacheInvalidationTime;

    /**
     * {@inheritdoc}
     */
    protected $_options = [
        'remote_backend' => '',
        'remote_backend_invalidation_time_id' => 'default_remote_backend_invalidation_time',
        'remote_backend_custom_naming' => true,
        'remote_backend_autoload' => true,
        'remote_backend_options' => [],
        'local_backend' => '',
        'local_backend_options' => [],
        'local_backend_custom_naming' => true,
        'local_backend_autoload' => true
    ];

    /**
     * @param array $options
     * @throws \Zend_Cache_Exception
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $universalOptions = array_diff_key($options, $this->_options);

        if ($this->_options['remote_backend'] === null) {
            \Zend_Cache::throwException('remote_backend option must be set');
        } elseif ($this->_options['remote_backend'] instanceof \Zend_Cache_Backend_ExtendedInterface) {
            $this->remote = $this->_options['remote_backend'];
        } else {
            $this->remote = \Zend_Cache::_makeBackend(
                $this->_options['remote_backend'],
                array_merge($universalOptions, $this->_options['remote_backend_options']),
                $this->_options['remote_backend_custom_naming'],
                $this->_options['remote_backend_autoload']
            );
            if (!($this->remote instanceof \Zend_Cache_Backend_ExtendedInterface)) {
                \Zend_Cache::throwException(
                    'remote_backend must implement the Zend_Cache_Backend_ExtendedInterface interface'
                );
            }
        }

        if ($this->_options['local_backend'] === null) {
            \Zend_Cache::throwException('local_backend option must be set');
        } elseif ($this->_options['local_backend'] instanceof \Zend_Cache_Backend_ExtendedInterface) {
            $this->local = $this->_options['local_backend'];
        } else {
            $this->local = \Zend_Cache::_makeBackend(
                $this->_options['local_backend'],
                array_merge($universalOptions, $this->_options['local_backend_options']),
                $this->_options['local_backend_custom_naming'],
                $this->_options['local_backend_autoload']
            );
            if (!($this->local instanceof \Zend_Cache_Backend_ExtendedInterface)) {
                \Zend_Cache::throwException(
                    'local_backend must implement the Zend_Cache_Backend_ExtendedInterface interface'
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDirectives($directives)
    {
        return $this->local->setDirectives($directives);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $localData = $this->local->load($id.':data');
        $remoteData = false;

        if (false === $localData) {
            $remoteData = $this->remote->load($id.':data');

            if (false === $remoteData) {
                return false;
            }
        } else {
            $remoteDataHash = $this->remote->load(
                $id . ':hash'
            );

            if (md5($localData) !== $remoteDataHash) {
                $localData = false;
                $remoteData = $this->remote->load($id.':data');
            }
        }

        if ($remoteData !== false) {
            $this->local->save($remoteData, $id.':data');
            $localData = $remoteData;
        }

        return $localData;
    }

    /**
     * {@inheritdoc}
     */
    public function test($id)
    {
        return $this->local->test($id.":data");
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $id, $tags = [], $specificLifetime = false)
    {
        $remHash = $this->remote->load($id. ":hash");

        if ($remHash !== false) {
            $remData = $this->remote->load($id.":data");

            return $this->local->save($remData, $id .':data', [], $specificLifetime);
        } else {

            $this->remote->save($data, $id.':data', $tags, $specificLifetime);
            $this->remote->save(md5($data), $id .':hash', $tags, $specificLifetime);

            return $this->local->save($data, $id .':data', [], $specificLifetime);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        return $this->remote->remove($id.':hash')
            && $this->remote->remove($id.':data')
            && $this->local->remove($id.':data');
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        return $this->remote->clean($mode, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        return $this->local->getIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return $this->local->getTags();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsMatchingTags($tags = [])
    {
        return $this->local->getIdsMatchingTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        return $this->local->getIdsNotMatchingTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        return $this->local->getIdsMatchingAnyTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getFillingPercentage()
    {
        return $this->local->getFillingPercentage();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadatas($id)
    {
        return $this->local->getMetadatas($id);
    }

    /**
     * {@inheritdoc}
     */
    public function touch($id, $extraLifetime)
    {
        return $this->local->touch($id, $extraLifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities()
    {
        return $this->local->getCapabilities();
    }
}
