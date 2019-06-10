<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * AggregateExceptionInterface Extension Point introduced to support Multiple Errors returned as a result of Validation
 * not mandating to inherit from AbstractAggregateException class
 *
 * @api
 * @since 101.0.7
 */
interface AggregateExceptionInterface
{
    /**
     * Returns LocalizedException[] array to be compatible with current Implementation in Web API which relies on
     * this behavior
     *
     * @see the \Magento\Framework\Webapi\Exception which receives $errors as a set of Localized Exceptions
     *
     * @return LocalizedException[]
     * @since 101.0.7
     */
    public function getErrors();
}
