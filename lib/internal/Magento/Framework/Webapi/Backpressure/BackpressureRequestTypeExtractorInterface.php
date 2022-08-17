<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Backpressure;

/**
 * Extracts request type ID from endpoints
 */
interface BackpressureRequestTypeExtractorInterface
{
    /**
     * Get type ID if possible.
     *
     * @param string $service
     * @param string $method
     * @param string $endpoint
     * @return string|null
     */
    public function extract(string $service, string $method, string $endpoint): ?string;
}
