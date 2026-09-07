<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use Exception;
use GraphQL\Error\ProvidesExtensions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use GraphQL\Error\ClientAware;

/**
 * Exception for GraphQL to be thrown when server error happens.
 */
class GraphQlServerException extends LocalizedException implements ClientAware, ProvidesExtensions
{
    private const string EXCEPTION_CATEGORY = 'graphql-server';

    /**
     * @param Phrase $phrase
     * @param Exception|null $cause
     * @param int $code
     * @param bool $isSafe
     */
    public function __construct(
        Phrase $phrase,
        ?Exception $cause = null,
        int $code = 0,
        private readonly bool $isSafe = true,
    ) {
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
    public function getExtensions(): array
    {
        return [
            'category' => self::EXCEPTION_CATEGORY,
        ];
    }
}
