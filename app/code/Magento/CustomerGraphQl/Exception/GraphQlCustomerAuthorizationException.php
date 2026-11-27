<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Exception;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Phrase;

/**
 * Exception thrown when the current GraphQL request is not made by an authorized customer
 *
 * @api
 */
class GraphQlCustomerAuthorizationException extends GraphQlAuthorizationException
{
    /**
     * @param Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     * @param bool $isSafe
     */
    public function __construct(
        ?Phrase $phrase = null,
        ?\Exception $cause = null,
        $code = 0,
        $isSafe = true
    ) {
        parent::__construct(
            $phrase ?? __('The current customer isn\'t authorized.'),
            $cause,
            $code,
            $isSafe
        );
    }
}
