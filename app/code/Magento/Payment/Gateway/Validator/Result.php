<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Phrase;

class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var Phrase[]
     */
    private $failsDescription;

    /**
     * @var string[]
     */
    private $errorCodes;

    /**
     * @param bool $isValid
     * @param array $failsDescription
     * @param array $errorCodes
     */
    public function __construct(
        $isValid,
        array $failsDescription = [],
        array $errorCodes = []
    ) {
        $this->isValid = (bool)$isValid;
        $this->failsDescription = $failsDescription;
        $this->errorCodes = $errorCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailsDescription(): array
    {
        return $this->failsDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }
}
