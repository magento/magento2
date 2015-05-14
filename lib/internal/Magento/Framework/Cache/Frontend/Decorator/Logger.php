<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cache frontend decorator that logger of cache invalidate
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\FrontendInterface;
use Psr\Log\LoggerInterface as LoggerHandler;

class Logger extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * @var LoggerHandler
     */
    private $logger;

    /**
     * @param FrontendInterface $frontend
     * @param LoggerHandler $logger
     */
    public function __construct(FrontendInterface $frontend, LoggerHandler $logger)
    {
        parent::__construct($frontend);
        $this->logger = $logger;
    }

     /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $result = parent::save($data, $identifier, $tags, $lifeTime);
        $this->logger->debug('cache_save', (array)$identifier + (array)$tags);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        $result = parent::remove($identifier);
        $this->logger->debug('cache_remove', (array)$identifier);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        $result = parent::clean($mode, $tags);
        $this->logger->debug('cache_clean', (array)$tags);
        return $result;
    }
}
