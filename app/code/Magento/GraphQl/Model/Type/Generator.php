<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\SchemaProvider;

/**
 * {@inheritdoc}
 */
class Generator implements GeneratorInterface
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var SchemaProvider
     */
    private $schemaProvider;

    /**
     * @param TypeFactory $typeFactory
     * @param SchemaProvider $schemaProvider
     */
    public function __construct(
        TypeFactory $typeFactory,
        SchemaProvider $schemaProvider
    ) {
        $this->typeFactory = $typeFactory;
        $this->schemaProvider = $schemaProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTypes()
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
