<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl;

/**
 * Wrapper for GraphQl Exception Formatter
 */
class ExceptionFormatter
{
    /**
     * Format a GraphQL error from an exception by converting it to array to conform to GraphQL spec.
     *
     * This method only exposes exception message when exception implements ClientAware interface
     * (or when debug flags are passed).
     *
     * @api
     * @param \Throwable $exception
     * @param bool|int $debug
     * @param string $internalErrorMessage
     * @return array
     * @throws \Throwable
     */
    public function create(\Throwable $exception, $debug = false, $internalErrorMessage = null)
    {
        return \GraphQL\Error\FormattedError::createFromException($exception, $debug, $internalErrorMessage);
    }
}
