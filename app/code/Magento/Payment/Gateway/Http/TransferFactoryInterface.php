<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Interface TransferFactoryInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 * @since 100.0.2
 */
interface TransferFactoryInterface
{
    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request);
}
