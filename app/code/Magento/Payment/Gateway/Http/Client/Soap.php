<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class Soap implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ConverterInterface | null
     */
    private $converter;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
        ConverterInterface $converter = null
    ) {
        $this->logger = $logger;
        $this->converter = $converter;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->logger->debug(['request' => $transferObject->getBody()]);

        $client = new \SoapClient($transferObject->getClientConfig()['wsdl'], ['trace' => true]);

        try {
            $client->__setSoapHeaders($transferObject->getHeaders());

            $result = $this->converter
                ? $this->converter->convert(
                    $client->__soapCall($transferObject->getMethod(), [$transferObject->getBody()])
                )
                : null;

            $this->logger->debug(['response' => $result]);
        } catch (\Exception $e) {
            $this->logger->debug(['trace' => $client->__getLastRequest()]);
            throw $e;
        }

        return $result;
    }
}
