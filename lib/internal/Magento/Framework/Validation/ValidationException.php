<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validation;

use Magento\Framework\Exception\AggregateExceptionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Validation exception with possibility to set several error messages
 *
 * ValidationException exists to be compatible with the Web-API (SOAP and REST) implementation which currently
 * uses Magento\Framework\Exception\AggregateExceptionInterface returned as a result of ServiceContracts call
 * to support Multi-Error response.
 *
 * @api
 * @since 101.0.7
 */
class ValidationException extends LocalizedException implements AggregateExceptionInterface
{
    /**
     * @var ValidationResult|null
     */
    private $validationResult;

    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param ValidationResult|null $validationResult
     */
    public function __construct(
        Phrase $phrase,
        ?\Exception $cause = null,
        $code = 0,
        ?ValidationResult $validationResult = null
    ) {
        parent::__construct($phrase, $cause, $code);
        $this->validationResult = $validationResult;
    }

    /**
     * @inheritdoc
     * @since 101.0.7
     */
    public function getErrors(): array
    {
        $localizedErrors = [];
        if (null !== $this->validationResult) {
            foreach ($this->validationResult->getErrors() as $error) {
                $localizedErrors[] = new LocalizedException($error);
            }
        }
        return $localizedErrors;
    }
}
