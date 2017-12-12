<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Contains high level structure for the composition of GraphQL query endpoints, and their arguments and resolvers.
 */
class QueryConfig
{
    /**
     * @var array
     */
    private $queryStructure;

    /**
     * @param array $queryStructure
     */
    public function __construct(array $queryStructure = [])
    {
        $this->queryStructure = $queryStructure;
    }

    /**
     * Retrieve field structure definitions for GraphQL queries
     *
     * @return array
     */
    public function getQueryFields()
    {
        return $this->queryStructure;
    }

    /**
     * Retrieves resolver instance configured by type name.
     *
     * @param string $type
     * @return ResolverInterface
     * @throws GraphQlInputException
     */
    public function getResolverClass($type)
    {
        if (!isset($this->queryStructure[$type]) || !isset($this->queryStructure[$type]['resolver'])) {
            throw new GraphQlInputException(__('A resolver is not defined for the type %1', $type));
        }

        return $this->queryStructure[$type]['resolver'];
    }
}
