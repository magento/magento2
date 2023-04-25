<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Error;

use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Phrase;

/**
 * Class for formatting internally-thrown errors if they match allowed exception types or using a default message if not
 */
class AggregateExceptionMessageFormatter
{
    /**
     * @var ExceptionMessageFormatterInterface[]
     */
    private $messageFormatters;

    /**
     * @param ExceptionMessageFormatterInterface[] $messageFormatters
     */
    public function __construct(array $messageFormatters)
    {
        $this->messageFormatters = $messageFormatters;
    }

    /**
     * Format a thrown exception message if it matches one of the supplied formatters, otherwise use a default message
     *
     * @param LocalizedException $e
     * @param Phrase $defaultMessage
     * @param string $messagePrefix
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return ClientAware
     */
    public function getFormatted(
        LocalizedException $e,
        Phrase $defaultMessage,
        string $messagePrefix,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info
    ): ClientAware {
        foreach ($this->messageFormatters as $formatter) {
            $formatted = $formatter->getFormatted($e, $messagePrefix, $field, $context, $info);
            if ($formatted) {
                return $formatted;
            }
        }
        return new GraphQlInputException($defaultMessage, $e);
    }
}
