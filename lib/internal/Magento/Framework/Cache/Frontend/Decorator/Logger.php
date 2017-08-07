<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\InvalidateLogger as LoggerHandler;

/**
 * Cache frontend decorator that logs cache invalidation actions
 */
class Logger extends Bare
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
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        $result = parent::remove($identifier);
        $this->log(compact('identifier'));
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        $result = parent::clean($mode, $tags, $mode);
        $this->log(compact('tags', 'mode'));
        return $result;
    }

    /**
     * @param mixed $args
     * @return void
     */
    public function log($args)
    {
        $this->logger->execute($args);
    }
}
