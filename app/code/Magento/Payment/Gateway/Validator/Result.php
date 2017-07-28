<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Phrase;

/**
 * Class \Magento\Payment\Gateway\Validator\Result
 *
 * @since 2.0.0
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     * @since 2.0.0
     */
    private $isValid;

    /**
     * @var Phrase[]
     * @since 2.0.0
     */
    private $failsDescription;

    /**
     * @param bool $isValid
     * @param array $failsDescription
     * @since 2.0.0
     */
    public function __construct(
        $isValid,
        array $failsDescription = []
    ) {
        $this->isValid = (bool)$isValid;
        $this->failsDescription = $failsDescription;
    }

    /**
     * Returns validation result
     *
     * @return bool
     * @since 2.0.0
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     * @since 2.0.0
     */
    public function getFailsDescription()
    {
        return $this->failsDescription;
    }
}
