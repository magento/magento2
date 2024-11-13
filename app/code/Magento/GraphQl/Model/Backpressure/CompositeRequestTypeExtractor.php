<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Extracts using other extractors
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
    public function extract(Field $field): ?string
    {
        foreach ($this->extractors as $extractor) {
            $type = $extractor->extract($field);
            if ($type) {
                return $type;
            }
        }

        return null;
    }
}
