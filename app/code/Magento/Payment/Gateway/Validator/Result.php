<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param bool $isValid
     * @param array $failsDescription
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
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription()
    {
        return $this->failsDescription;
    }
}
