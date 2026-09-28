<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface ValidatorPoolInterface
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 100.0.2
 */
interface ValidatorPoolInterface
{
    /**
     * Returns configured validator
     *
     * @param string $code
     * @return \Magento\Payment\Gateway\Validator\ValidatorInterface
     * @throws NotFoundException
     */
    public function get($code);
}
