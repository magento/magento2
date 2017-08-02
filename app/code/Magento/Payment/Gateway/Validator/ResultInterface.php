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
 * @since 2.0.0
 */
interface ResultInterface
{
    /**
     * Returns validation result
     *
     * @return bool
     * @since 2.0.0
     */
    public function isValid();

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     * @since 2.0.0
     */
    public function getFailsDescription();
}
