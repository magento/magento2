<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GatewayCommand
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class GatewayCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorMessageMapperInterface
     */
    private $errorMessageMapper;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param HandlerInterface $handler
     * @param ValidatorInterface $validator
     * @param ErrorMessageMapperInterface|null $errorMessageMapper
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ErrorMessageMapperInterface $errorMessageMapper = null
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->errorMessageMapper = $errorMessageMapper;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws CommandException
     * @throws ClientException
     * @throws ConverterException
     */
    public function execute(array $commandSubject)
    {
        // @TODO implement exceptions catching
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->client->placeRequest($transferO);
        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                $this->processErrors($result);
            }
        }

        if ($this->handler) {
            $this->handler->handle(
                $commandSubject,
                $response
            );
        }
    }

    /**
     * Tries to map error messages from validation result and logs processed message.
     * Throws an exception with mapped message or default error.
     *
     * @param ResultInterface $result
     * @throws CommandException
     */
    private function processErrors(ResultInterface $result)
    {
        $messages = [];
        $errorsSource = array_merge($result->getErrorCodes(), $result->getFailsDescription());
        foreach ($errorsSource as $errorCodeOrMessage) {
            $errorCodeOrMessage = (string) $errorCodeOrMessage;

            // error messages mapper can be not configured if payment method doesn't have custom error messages.
            if ($this->errorMessageMapper !== null) {
                $mapped = (string) $this->errorMessageMapper->getMessage($errorCodeOrMessage);
                if (!empty($mapped)) {
                    $messages[] = $mapped;
                    $errorCodeOrMessage = $mapped;
                }
            }
            $this->logger->critical('Payment Error: ' . $errorCodeOrMessage);
        }

        throw new CommandException(
            !empty($messages)
                ? __(implode(PHP_EOL, $messages))
                : __('Transaction has been declined. Please try again later.')
        );
    }
}
