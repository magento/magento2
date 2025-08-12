<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Exception;
use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Exception to GraphQL that is thrown when the user submits too many requests
 */
class GraphQlTooManyRequestsException extends LocalizedException implements ClientAware
{
    public const EXCEPTION_CATEGORY = 'graphql-too-many-requests';

    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * @param Phrase $phrase
     * @param Exception|null $cause
     * @param int $code
     * @param bool $isSafe
     */
    public function __construct(Phrase $phrase, ?Exception $cause = null, int $code = 0, bool $isSafe = true)
    {
        $this->isSafe = $isSafe;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @inheritdoc
     */
    public function isClientSafe(): bool
    {
        return $this->isSafe;
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): string
    {
        return self::EXCEPTION_CATEGORY;
    }
}
