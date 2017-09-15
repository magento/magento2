<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * Exception with possibility to set several error messages
 *
 * @api
 */
interface AggregateExceptionInterface
{
    /**
     * Get the array of LocalizedException objects. Get an empty array if no errors were added
     *
     * @return LocalizedException[]
     */
    public function getErrors();
}
