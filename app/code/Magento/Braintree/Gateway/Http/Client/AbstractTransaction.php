<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Gateway\Http\Client;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTransaction
 * @since 2.1.0
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     * @since 2.1.0
     */
    protected $logger;

    /**
     * @var Logger
     * @since 2.1.0
     */
    protected $customLogger;

    /**
     * @var BraintreeAdapter
     * @since 2.1.0
     */
    protected $adapter;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param BraintreeAdapter $transaction
     * @since 2.1.0
     */
    public function __construct(LoggerInterface $logger, Logger $customLogger, BraintreeAdapter $adapter)
    {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
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
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
            $this->customLogger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     * @since 2.1.0
     */
    abstract protected function process(array $data);
}
