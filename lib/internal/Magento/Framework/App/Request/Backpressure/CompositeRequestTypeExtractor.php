<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Uses other extractors
 */
class CompositeRequestTypeExtractor implements RequestTypeExtractorInterface
{
    /**
     * @var RequestTypeExtractorInterface[]
     */
    private array $extractors;

    /**
     * @param RequestTypeExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * @inheritDoc
     */
    public function extract(RequestInterface $request, ActionInterface $action): ?string
    {
        foreach ($this->extractors as $extractor) {
            $type = $extractor->extract($request, $action);
            if ($type) {
                return $type;
            }
        }

        return null;
    }
}
