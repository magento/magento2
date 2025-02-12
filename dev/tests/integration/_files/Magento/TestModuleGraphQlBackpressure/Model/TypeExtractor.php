<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleGraphQlBackpressure\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Backpressure\RequestTypeExtractorInterface;

class TypeExtractor implements RequestTypeExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(Field $field): ?string
    {
        if ($field->getResolver() == TestServiceResolver::class) {
            return 'testgraphqlbackpressure';
        }

        return null;
    }
}
