<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\GraphQl\SchemaProvider;

/**
 * {@inheritdoc}
 */
class Generator implements GeneratorInterface
{
    /**
     * @var SchemaProvider
     */
    private $schemaProvider;

    /**
     * @param SchemaProvider $schemaProvider
     */
    public function __construct(
        SchemaProvider $schemaProvider
    ) {
        $this->schemaProvider = $schemaProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTypes() : array
    {
        $types = [];
        foreach ($this->schemaProvider->getTypes() as $name => $type) {
            if ($name == 'Query') {
                continue;
            }
            $types[] = $type;
        }
        return [
            'fields' => $this->schemaProvider->getQuery()->getFields(),
            'types' => $types
        ];
    }
}
