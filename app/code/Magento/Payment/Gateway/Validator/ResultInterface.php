<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Phrase;

/**
 * Interface ResultInterface
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 100.0.2
 */
interface ResultInterface
{
    /**
     * Returns validation result
     *
     * @return bool
     */
    public function isValid();

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription();

    /**
     * Returns list of error codes.
     *
     * @return string[]
     * @since 100.3.0
     */
    public function getErrorCodes();
}
