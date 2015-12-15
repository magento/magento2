<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Http\Client;

use Magento\BraintreeTwo\Gateway\Request\CaptureDataBuilder;
use Magento\Eway\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransactionClone
 */
class TransactionClone extends TransactionSale
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
        $transactionId = $data[CaptureDataBuilder::TRANSACTION_ID];
        unset($data[CaptureDataBuilder::TRANSACTION_ID]);

        try {
            $response['object'] = $this->transaction->cloneTransaction($transactionId, $data);
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