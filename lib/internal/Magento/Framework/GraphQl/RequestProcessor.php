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
class RequestProcessor
{
    /**
     * Processes a GraphQl request according to defined schema
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
        return \GraphQL\GraphQL::execute(
            $schema,
            $source,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName
        );
    }
}
