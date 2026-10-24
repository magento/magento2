<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Throwable;

class UspsPaymentAuthToken extends AbstractCarrier
{
    private const CONTENT_TYPE_JSON = 'application/json';
    private const AUTHORIZATION_BEARER = 'Bearer ';
    private const ERROR_LOG_MESSAGE = '---Exception from auth api---';

    /**
     * @var AsyncClientInterface
     */
    private mixed $asyncHttpClient;

    /**
     * UspsPaymentAuthToken constructor.
     *
     * @param AsyncClientInterface|null $asyncHttpClient
     */
    public function __construct(
        ?AsyncClientInterface $asyncHttpClient = null,
    ) {
        $this->asyncHttpClient = $asyncHttpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
    }

    /**
     * Payment Auth Token Generation
     *
     * @param String $token
     * @param string $authUrl
     * @param array $accountInfo
     * @return string
     * @throws Throwable
     */
    public function getPaymentAuthToken(string $token, string $authUrl, array $accountInfo): string
    {
        $headers = [
            'Content-Type' => self::CONTENT_TYPE_JSON,
            'Authorization' => self::AUTHORIZATION_BEARER . $token,
        ];

        $requestParam = [
            'roles' => [
                [
                    "roleName" => "PAYER",
                    "CRID" => $accountInfo['CRID'],
                    "accountNumber" => $accountInfo['accountNumber'],
                    "accountType" => $accountInfo['accountType'],
                ],
                [
                    "roleName" => "LABEL_OWNER",
                    "CRID" => $accountInfo['CRID'],
                    "MID" => $accountInfo['MID'],
                    "manifestMID" => $accountInfo['manifestMID'],
                ]
            ],
        ];

        try {
            $asyncResponse = $this->asyncHttpClient->request(new Request(
                $authUrl,
                Request::METHOD_POST,
                $headers,
                json_encode($requestParam)
            ));

            $responseResult = $asyncResponse->get();
            $responseData = $responseResult->getBody();
            $responseData = json_decode($responseData);

            if (isset($responseData->paymentAuthorizationToken)) {
                return $responseData->paymentAuthorizationToken;
            } else {
                $debugData = ['request_type' => 'Payment Access Token Request for Label generation',
                    'result' => $responseData];
                $this->_debug($debugData);
                $errorMessage = $responseData->error->message ?? 'Unknown error occurred';
                throw new LocalizedException(__($errorMessage));

            }
        } catch (HttpException $e) {
            $this->_debug(self::ERROR_LOG_MESSAGE . $e->getMessage());
            throw new LocalizedException(__('HTTP Exception: ' . $e->getMessage()));
        } catch (Throwable $e) {
            $this->_debug(self::ERROR_LOG_MESSAGE . $e->getMessage());
            throw new LocalizedException(__('Error: ' . $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable
     */
    public function collectRates(RateRequest $request) : string
    {
        return ''; // This block is empty as not required.
    }
}
