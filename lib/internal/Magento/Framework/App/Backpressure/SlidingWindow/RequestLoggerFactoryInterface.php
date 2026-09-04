<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\Exception\RuntimeException;

/**
 * Creates Backpressure Logger by type
 */
interface RequestLoggerFactoryInterface
{
    /**
     * Creates Backpressure Logger object by type
     *
     * @param string $type
     * @return RequestLoggerInterface
     * @throws RuntimeException
     */
    public function create(string $type): RequestLoggerInterface;
}
