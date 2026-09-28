<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class UnsupportedMediaTypeException extends LocalizedException implements InvalidRequestInterface, ClientAware
{
    /**
     * @param Phrase $phrase
     * @param \Exception|null $cause
     * @param int $code
     * @param bool $isSafe
     */
    public function __construct(
        Phrase $phrase,
        ?\Exception $cause = null,
        int $code = 0,
        private readonly bool $isSafe = true,
    ) {
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @inheritdoc
     */
    public function getStatusCode(): int
    {
        return 415;
    }

    /**
     * @inheritdoc
     */
    public function isClientSafe(): bool
    {
        return $this->isSafe;
    }
}
