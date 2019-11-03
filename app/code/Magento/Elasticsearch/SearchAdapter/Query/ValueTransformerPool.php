<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query;

/**
 * Pool of value transformers.
 */
class ValueTransformerPool
{
    /**
     * @var ValueTransformerInterface[]
     */
    private $transformers;

    /**
     * @param ValueTransformerInterface[] $valueTransformers
     */
    public function __construct(array $valueTransformers = [])
    {
        foreach ($valueTransformers as $valueTransformer) {
            if (!$valueTransformer instanceof ValueTransformerInterface) {
                throw new \InvalidArgumentException(
                    \sprintf('"%s" is not a instance of ValueTransformerInterface.', get_class($valueTransformer))
                );
            }
        }

        $this->transformers = $valueTransformers;
    }

    /**
     * Get value transformer related to field type.
     *
     * @param string $fieldType
     * @return ValueTransformerInterface
     */
    public function get(string $fieldType): ValueTransformerInterface
    {
        return $this->transformers[$fieldType] ?? $this->transformers['default'];
    }
}
