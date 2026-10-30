<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestModuleWebapiBackpressure\Model;

use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;
use Magento\TestModuleWebapiBackpressure\Api\TestReadServiceInterface;

class TypeExtractor implements BackpressureRequestTypeExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(string $service, string $method, string $endpoint): ?string
    {
        if ($service == TestReadServiceInterface::class) {
            return 'testwebapibackpressure';
        }

        return null;
    }
}
