<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Gateway\Http\Client;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     * @var BraintreeAdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param BraintreeAdapter $adapter
     * @param BraintreeAdapterFactory|null $adapterFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        BraintreeAdapter $adapter,
        BraintreeAdapterFactory $adapterFactory = null
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapterFactory = $adapterFactory ?: ObjectManager::getInstance()->get(BraintreeAdapterFactory::class);
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
     */
    abstract protected function process(array $data);
}
