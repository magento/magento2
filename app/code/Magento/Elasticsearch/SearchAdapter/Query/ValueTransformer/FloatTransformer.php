<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer;

use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerInterface;

/**
 * Value transformer for float type fields.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
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
