<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefrontElasticsearch\SearchAdapter\Query\ValueTransformer;

use Magento\SearchStorefrontElasticsearch\SearchAdapter\Query\ValueTransformerInterface;

/**
 * Value transformer for float type fields.
 */
class FloatTransformer implements ValueTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(string $value): ?float
    {
        return \is_numeric($value) ? (float) $value : null;
    }
}
