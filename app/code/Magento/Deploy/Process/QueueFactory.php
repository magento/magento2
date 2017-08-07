<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Process;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Factory class for @see \Magento\Deploy\Process\Queue
 * @since 2.2.0
 */
class QueueFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Instance type to create
     *
     * @var string
     * @since 2.2.0
     */
    private $type;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $type
     * @since 2.2.0
     */
    public function __construct(ObjectManagerInterface $objectManager, $type = Queue::class)
    {
        $this->objectManager = $objectManager;
        $this->type = $type;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $arguments
     * @return Queue
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function create(array $arguments = [])
    {
        $queue = $this->objectManager->create($this->type, $arguments);
        if (!$queue instanceof Queue) {
            throw new LocalizedException(
                new Phrase("Wrong queue type specified.")
            );
        }
        return $queue;
    }
}
