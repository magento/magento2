<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Interface ConverterInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 * @since 2.0.0
 */
interface ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     * @since 2.0.0
     */
    public function convert($response);
}
