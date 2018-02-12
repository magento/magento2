<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;

/**
 * {@inheritdoc}
 */
class Generator implements GeneratorInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var array
     */
    private $typeMap;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param array $typeMap
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $typePool, array $typeMap, TypeFactory $typeFactory)
    {
        $this->typePool = $typePool;
        $this->typeMap = $typeMap;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTypes(string $typeName)
    {
        $types = [];
        if (!isset($this->typeMap[$typeName])) {
            throw new GraphQlInputException(__('Invalid GraphQL query name.'));
        }
        /** @var HandlerInterface $handler */
        foreach ($this->typeMap['types'] as $handler) {
            $type = $handler->getType();
            $this->typePool->registerType($type);
            $types[] = $type;
        }

        $fields = [];
        $mappedFields = $this->typeMap[$typeName];
        foreach ($mappedFields['fields'] as $fieldName => $values) {
            $arguments = [];
            if (!isset($values['args'])) {
                $values['args'] = [];
            }
            foreach ($values['args'] as $argName => $argType) {
                // Replace '[]' with an 's' to express plurality
                $realArgName = str_replace('!', '', str_replace('[]', 's', $argName));
                $arguments[$realArgName] = ['type' => $this->decorateType($argName, $argType)];
            }

            $fields[$fieldName] = ['type' => $this->typePool->getType($values['type']), 'args' => $arguments];
        }

        return ['fields' => $fields, 'types' => $types];
    }

    /**
     * Decorate type as non-null or as a list if formatted (with a '!' or '[]' appended, respectively)
     *
     * @param string $argumentName
     * @param string $argumentType
     * @return TypeInterface|\GraphQL\Type\Definition\Type
     */
    private function decorateType(string $argumentName, string $argumentType)
    {
        $type = $this->typePool->getType($argumentType);
        $type = strpos($argumentName, '!') !== false ? $this->typeFactory->createNonNull($type) : $type;
        $type = strpos($argumentName, '[]') !== false ? $this->typeFactory->createList($type) : $type;

        return $type;
    }
}
