<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BraintreeTwo\Gateway\Http\Client;

use Magento\BraintreeTwo\Model\Adapter\BraintreeTransaction;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var BraintreeTransaction
     */
    protected $transaction;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param BraintreeTransaction $transaction
     */
    public function __construct(Logger $logger, BraintreeTransaction $transaction)
    {
        $this->logger = $logger;
        $this->transaction = $transaction;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            throw new ClientException(__(
                $e->getMessage() ?: 'Sorry, but something went wrong'
            ));
        } finally {
            $log['response'] = (array )$response['object'];
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    abstract protected function process(array $data);
}