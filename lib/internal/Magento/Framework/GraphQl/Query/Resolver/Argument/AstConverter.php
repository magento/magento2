<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\NodeList;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\ClauseFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Connective;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\ConnectiveFactory;

/**
 * Converts the input of an object type to a @see Connective format by using entity attributes to identify clauses
 */
class AstConverter
{
    /**
     * @var ClauseFactory
     */
    private $clauseFactory;

    /**
     * @var ConnectiveFactory
     */
    private $connectiveFactory;

    /**
     * @var FieldEntityAttributesPool
     */
    private $fieldEntityAttributesPool;

    /**
     * @param ClauseFactory $clauseFactory
     * @param ConnectiveFactory $connectiveFactory
     * @param FieldEntityAttributesPool $fieldEntityAttributesPool
     */
    public function __construct(
        ClauseFactory $clauseFactory,
        ConnectiveFactory $connectiveFactory,
        FieldEntityAttributesPool $fieldEntityAttributesPool
    ) {
        $this->clauseFactory = $clauseFactory;
        $this->connectiveFactory = $connectiveFactory;
        $this->fieldEntityAttributesPool = $fieldEntityAttributesPool;
    }

    /**
     * Get a clause from an AST
     *
     * @param string $fieldName
     * @param array $arguments
     * @return array
     */
    public function getClausesFromAst(string $fieldName, array $arguments) : array
    {
        $attributes = $this->fieldEntityAttributesPool->getEntityAttributesForEntityFromField($fieldName);
        $conditions = [];
        foreach ($arguments as $argumentName => $argument) {
            if (in_array($argumentName, $attributes)) {
                foreach ($argument as $clauseType => $clause) {
                    if (is_array($clause)) {
                        $value = [];
                        foreach ($clause as $item) {
                            $value[] = $item;
                        }
                    } else {
                        $value = $clause;
                    }
                    $conditions[] = $this->clauseFactory->create(
                        $argumentName,
                        $clauseType,
                        $value
                    );
                }
            } else {
                $conditions[] =
                    $this->connectiveFactory->create(
                        $this->getClausesFromAst($fieldName, $argument),
                        $argumentName
                    );
            }
        }
        return $conditions;
    }
}
