<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Interface ConverterInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 * @since 100.0.2
 */
interface ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function convert($response);
}
