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
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Interface for formatting reportable internal error messages
 */
interface ExceptionMessageFormatterInterface
{
    /**
     * Re-throws a matching internal exception with the appropriate GraphQl exception type and user-safe message
     *
     * @param LocalizedException $e
     * @param string $messagePrefix
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     *
     * @return ClientAware|null
     */
    public function getFormatted(
        LocalizedException $e,
        string $messagePrefix,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info
    ): ?ClientAware;
}
