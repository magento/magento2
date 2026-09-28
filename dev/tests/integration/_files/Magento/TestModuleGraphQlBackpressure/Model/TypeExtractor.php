<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
