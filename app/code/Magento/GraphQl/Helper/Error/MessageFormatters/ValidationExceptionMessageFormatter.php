<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Error\MessageFormatters;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\Exception as FrameworkValidatorException;
use Magento\GraphQl\Helper\Error\ExceptionMessageFormatterInterface;

/**
 * Check if an internally-thrown exception is from a validation failure and re-throw with the message intact if so
 */
class ValidationExceptionMessageFormatter implements ExceptionMessageFormatterInterface
{
    /**
     * If the thrown exception was from a known validation failure, allow the message to go through
     *
     * @param LocalizedException $e
     * @param string $messagePrefix
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return GraphQlInputException|null
     */
    public function getFormatted(
        LocalizedException $e,
        string $messagePrefix,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info
    ): ?GraphQlInputException {
        if ($e instanceof ValidatorException
            || $e instanceof ValidationException
            || $e instanceof FrameworkValidatorException) {
            return new GraphQlInputException(__("$messagePrefix: %message", ['message' => $e->getMessage()]), $e);
        }
        return null;
    }
}
