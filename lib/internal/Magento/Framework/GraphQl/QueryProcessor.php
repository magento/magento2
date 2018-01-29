<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Schema;

/**
 * Wrapper for GraphQl execution of a schema
 */
class QueryProcessor
{
    /**
     * @var ExceptionFormatter
     */
    private $exceptionFormatter;

    /**
     * @param ExceptionFormatter $exceptionFormatter
     */
    public function __construct(ExceptionFormatter $exceptionFormatter)
    {
        $this->exceptionFormatter = $exceptionFormatter;
    }

    /**
     * Process a GraphQl query according to defined schema
     *
     * @param Schema $schema
     * @param string $source
     * @param mixed $rootValue
     * @param mixed $contextValue
     * @param array|null $variableValues
     * @param string|null $operationName
     * @return Promise|array
     */
    public function process(
        Schema $schema,
        $source,
        $rootValue = null,
        $contextValue = null,
        $variableValues = null,
        $operationName = null
    ) {
        return \GraphQL\GraphQL::executeQuery(
            $schema,
            $source,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName
        )->toArray(
            $this->exceptionFormatter->shouldShowDetail() ?
                \GraphQL\Error\Debug::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\Debug::INCLUDE_TRACE : false
        );
    }
}
