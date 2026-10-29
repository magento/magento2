<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Extracts type ID for backpressure context
 */
interface RequestTypeExtractorInterface
{
    /**
     * Extract type ID if possible
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @return string|null
     */
    public function extract(RequestInterface $request, ActionInterface $action): ?string;
}
