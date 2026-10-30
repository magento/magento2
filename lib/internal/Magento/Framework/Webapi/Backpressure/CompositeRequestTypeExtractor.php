<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Backpressure;

/**
 * Uses other extractors
 */
class CompositeRequestTypeExtractor implements BackpressureRequestTypeExtractorInterface
{
    /**
     * @var BackpressureRequestTypeExtractorInterface[]
     */
    private array $extractors;

    /**
     * @param BackpressureRequestTypeExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * @inheritDoc
     */
    public function extract(string $service, string $method, string $endpoint): ?string
    {
        foreach ($this->extractors as $extractor) {
            $type = $extractor->extract($service, $method, $endpoint);
            if ($type) {
                return $type;
            }
        }

        return null;
    }
}
