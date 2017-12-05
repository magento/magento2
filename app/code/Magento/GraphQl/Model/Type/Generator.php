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
use Magento\GraphQl\Model\Type\Config as TypeConfig;
use Magento\GraphQl\Model\Query\Config as QueryConfig;

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
     * @var TypeConfig
     */
    private $typeConfig;

    /**
     * @var QueryConfig
     */
    private $queryConfig;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var HandlerFactory
     */
    private $handlerFactory;

    /**
     * @param Pool $typePool
     * @param TypeConfig $typeConfig
     * @param QueryConfig $queryConfig
     * @param TypeFactory $typeFactory
     * @param HandlerFactory $handlerFactory
     */
    public function __construct(
        Pool $typePool,
        TypeConfig $typeConfig,
        QueryConfig $queryConfig,
        TypeFactory $typeFactory,
        HandlerFactory $handlerFactory
    ) {
        $this->typePool = $typePool;
        $this->typeConfig = $typeConfig;
        $this->queryConfig = $queryConfig;
        $this->typeFactory = $typeFactory;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTypes(string $typeName)
    {
        $types = [];
        /** @var string $handlerName */
        foreach ($this->typeConfig->getTypes() as $typeName => $handlerName) {
            $handler = $this->handlerFactory->create($handlerName);
            $type = $handler->getType();
            if (!$this->typePool->isTypeRegistered($typeName)) {
                $this->typePool->registerType($type);
                $types[] = $type;
            } else {
                $types[] = $this->typePool->getType($typeName);
            }
        }

        $fields = [];
        foreach ($this->queryConfig->getQueryFields() as $fieldName => $values) {
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
        $type = strpos($argumentName, '[]') !== false ? $this->typeFactory->createList($type) : $type;
        $type = strpos($argumentName, '!') !== false ? $this->typeFactory->createNonNull($type) : $type;

        return $type;
    }
}
