<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Error\MessageFormatters;

use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Helper\Error\ExceptionMessageFormatterInterface;

/**
 * Check if a thrown exception is already formatted for GraphQL and re-throw with no changes if so
 */
class GraphQlExceptionMessageFormatter implements ExceptionMessageFormatterInterface
{
    /**
     * If the thrown exception is already formatted for GraphQl, re-throw it with no changes
     *
     * @param LocalizedException $e
     * @param string $messagePrefix
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return GraphQlAlreadyExistsException|GraphQlAuthenticationException|GraphQlAuthorizationException|GraphQlInputException|GraphQlNoSuchEntityException|null
     */
    public function getFormatted(
        LocalizedException $e,
        string $messagePrefix,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info
    ): ?ClientAware {
        if ($e instanceof GraphQlAlreadyExistsException
            || $e instanceof GraphQlAuthenticationException
            || $e instanceof GraphQlAuthorizationException
            || $e instanceof GraphQlInputException
            || $e instanceof GraphQlNoSuchEntityException) {
            return $e;
        }
        return null;
    }
}
