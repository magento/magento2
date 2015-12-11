<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Http\Client;

use Magento\BraintreeTwo\Gateway\Request\CaptureDataBuilder;
use Magento\BraintreeTwo\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransactionSubmitForSettlement
 */
class TransactionSubmitForSettlement extends TransactionSale
{
    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => self::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->transaction->submitForSettlement(
                $data[CaptureDataBuilder::TRANSACTION_ID],
                $data[PaymentDataBuilder::AMOUNT]
            );
        } catch (\Exception $e) {
            throw new ClientException(__(
                $e->getMessage() ?: 'Sorry, but something went wrong'
            ));
        } finally {
            $log['response'] = (array) $response['object'];
            $this->logger->debug($log);
        }

        return $response;
    }
}