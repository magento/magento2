<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image\Process;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Factory class for @see \Magento\Catalog\Model\Product\Image\Process\Queue
 */
class QueueFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance type to create
     *
     * @var string
     */
    protected $type;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $type
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
