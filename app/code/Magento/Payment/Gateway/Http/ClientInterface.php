<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

use Magento\Payment\Gateway\Response;

/**
 * Interface ClientInterface
 * @package Magento\Payment\Gateway\Http
 * @api
 */
interface ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject);
}
