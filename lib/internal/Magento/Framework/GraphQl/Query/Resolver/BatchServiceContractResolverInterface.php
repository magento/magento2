<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Resolve multiple brunches/leaves by executing a batch service contract.
 */
interface BatchServiceContractResolverInterface
{
    /**
     * Service contract to use, 1st element - class, 2nd - method.
     *
     * @return array
     */
    public function getServiceContract(): array;

    /**
     * Convert GraphQL arguments into a batch service contract argument item.
     *
     * @param ResolveRequestInterface $request
     * @return object
     * @throws GraphQlInputException
     */
    public function convertToServiceArgument(ResolveRequestInterface $request);

    /**
     * Convert service contract result item into resolved brunch/leaf.
     *
     * @param object $result Result item returned from service contract.
     * @param ResolveRequestInterface $request Initial request.
     * @return mixed|Value Resolved response.
     * @throws GraphQlInputException
     */
    public function convertFromServiceResult($result, ResolveRequestInterface $request);
}
