<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface ValidatorPoolInterface
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 2.0.0
 */
interface ValidatorPoolInterface
{
    /**
     * Returns configured validator
     *
     * @param string $code
     * @return \Magento\Payment\Gateway\Validator\ValidatorInterface
     * @throws NotFoundException
     * @since 2.0.0
     */
    public function get($code);
}
