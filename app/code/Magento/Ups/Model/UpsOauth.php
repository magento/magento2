<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class UpsOauth
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    public const TEST_AUTH_URL = 'https://onlinetools.ups.com/security/v1/oauth/token';

    /**
     *
     * @param Client $httpClient
     * @param StoreManagerInterface $storeManager
     * @param Json $jsonSerializer
     */
    public function __construct(
        Client                $httpClient,
        StoreManagerInterface $storeManager,
        Json                  $jsonSerializer
    ) {
        $this->httpClient = $httpClient;
        $this->storeManager = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Token Generation
     *
     * @param String $clientId
     * @param String $clientSecret
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAccessToken($clientId, $clientSecret)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $scope = $this->storeManager->getStore($storeId)->getCode();

        try {
            $response = $this->httpClient->request('POST', self::TEST_AUTH_URL, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'x-merchant-id' => 'string',
                    'Authorization' => 'Basic ' . base64_encode("$clientId:$clientSecret")
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => $scope,
                ],
            ]);

            $body = $response->getBody()->getContents();
            $responseData = $this->jsonSerializer->unserialize($body);

            if (isset($responseData['access_token'])) {
                return $responseData['access_token'];
            } else {
                throw new LocalizedException(__('Unable to retrieve access token.'));
            }
        } catch (GuzzleException $e) {
            throw new LocalizedException(__('Error occurred: %1', $e->getMessage()));
        }
    }
}
