<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryDepth;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Schema;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

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
     * @param ContextInterface $contextValue
     * @param array|null $variableValues
     * @param string|null $operationName
     * @return Promise|array
     */
    public function process(
        Schema $schema,
        string $source,
        ContextInterface $contextValue = null,
        array $variableValues = null,
        string $operationName = null
    ) : array {
        if (!$this->exceptionFormatter->shouldShowDetail()) {
            DocumentValidator::addRule(new QueryDepth(10));
            DocumentValidator::addRule(new DisableIntrospection());
        }
        $rootValue = null;
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
