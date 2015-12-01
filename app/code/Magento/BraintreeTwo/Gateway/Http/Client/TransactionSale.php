<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Http\Client;

use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\BraintreeTwo\Model\Adapter\BraintreeTransaction;

/**
 * Class TransactionSale
 */
class TransactionSale implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var BraintreeTransaction
     */
    private $braintreeTransaction;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param BraintreeTransaction $braintreeTransaction
     */
    public function __construct(
        Logger $logger,
        BraintreeTransaction $braintreeTransaction
    ) {
        $this->logger = $logger;
        $this->braintreeTransaction = $braintreeTransaction;
    }

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
            $response['object'] = $this->braintreeTransaction->sale($data);
        } catch (\Exception $e) {
            throw new ClientException(__(
                $e->getMessage() ?: 'Transaction has been declined, please, try again later.'
            ));
        } finally {
            $log['response'] = json_decode(json_encode($response['object']), true);
            $this->logger->debug($log);
        }

        return $response;
    }
}
