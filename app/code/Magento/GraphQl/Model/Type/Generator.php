<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Type\Handler\Pool;

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
     * @param Pool $typePool
     * @param array $typeMap
     */
    public function __construct(Pool $typePool, array $typeMap)
    {
        $this->typePool = $typePool;
        $this->typeMap = $typeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTypes(string $typeName)
    {
        $types = [];
        if (!isset($this->typeMap[$typeName])) {
            throw new LocalizedException(__('Invalid GraphQL query name.'));
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
     * @return Type
     */
    private function decorateType(string $argumentName, string $argumentType)
    {
        $type = $this->typePool->getType($argumentType);
        $type = strpos($argumentName, '!') !== false ? new NonNull($type) : $type;
        $type = strpos($argumentName, '[]') !== false ? new ListOfType($type) : $type;

        return $type;
    }
}
