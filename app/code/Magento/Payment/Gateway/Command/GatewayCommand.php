<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Request;
use Magento\Payment\Gateway\Response;

class GatewayCommand implements CommandInterface
{
    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilderInterface
     */
    private $transferBuilder;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface
     */
    private $gateway;

    /**
     * @var \Magento\Payment\Gateway\Response\HandlerInterface
     */
    private $responseHandler;

    /**
     * @param \Magento\Payment\Gateway\Request\BuilderInterface $requestBuilder
     * @param \Magento\Payment\Gateway\Http\TransferBuilderInterface $transferBuilder
     * @param \Magento\Payment\Gateway\Http\ClientInterface $gateway
     * @param \Magento\Payment\Gateway\Response\HandlerInterface $responseHandler
     */
    public function __construct(
        \Magento\Payment\Gateway\Request\BuilderInterface $requestBuilder,
        \Magento\Payment\Gateway\Http\TransferBuilderInterface $transferBuilder,
        ClientInterface $gateway,
        \Magento\Payment\Gateway\Response\HandlerInterface $responseHandler
    ) {

        $this->requestBuilder = $requestBuilder;
        $this->transferBuilder = $transferBuilder;
        $this->gateway = $gateway;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     */
    public function execute(array $commandSubject)
    {
        // @TODO implement exceptions catching
        $transferO = $this->transferBuilder->build(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->gateway->placeRequest($transferO);

        $this->responseHandler->handle(
            $commandSubject,
            $response
        );
    }
}
