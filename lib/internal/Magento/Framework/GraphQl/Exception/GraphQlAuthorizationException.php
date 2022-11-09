<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Exception for GraphQL to be thrown when authorization fails
 *
 * @api
 */
class GraphQlAuthorizationException extends AuthorizationException implements \GraphQL\Error\ClientAware
{
    const EXCEPTION_CATEGORY = 'graphql-authorization';

    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * Initialize object
     *
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param boolean $isSafe
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0, $isSafe = true)
    {
        $this->isSafe = $isSafe;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @inheritdoc
     */
    public function isClientSafe() : bool
    {
        return $this->isSafe;
    }

    /**
     * @inheritdoc
     */
    public function getCategory() : string
    {
        return self::EXCEPTION_CATEGORY;
    }
}
