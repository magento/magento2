<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Creates Backpressure Logger by type
 */
class RequestLoggerFactory implements RequestLoggerFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var array
     */
    private array $types;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(ObjectManagerInterface $objectManager, array $types)
    {
        $this->types = $types;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     *
     * @param string $type
     * @return RequestLoggerInterface
     * @throws RuntimeException
     */
    public function create(string $type): RequestLoggerInterface
    {
        if (isset($this->types[$type])) {
            return $this->objectManager->create($this->types[$type]);
        }

        throw new RuntimeException(__('Invalid request logger type: %1', $type));
    }
}
