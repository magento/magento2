<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;

class UpsAuth
{
    public const TEST_AUTH_URL = 'https://wwwcie.ups.com/security/v1/oauth/token';

    /**
     * @var AsyncClientInterface
     */
    private $asyncHttpClient;

    /**
     * @param AsyncClientInterface|null $asyncHttpClient
     */
    public function __construct(AsyncClientInterface $asyncHttpClient = null)
    {
        $this->asyncHttpClient = $asyncHttpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
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
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'x-merchant-id' => 'string',
            'Authorization' => 'Basic ' . base64_encode("$clientId:$clientSecret"),
        ];
        $authPayload = http_build_query([
            'grant_type' => 'client_credentials',
        ]);
        try {
            $asyncResponse = $this->asyncHttpClient->request(new Request(
                self::TEST_AUTH_URL,
                Request::METHOD_POST,
                $headers,
                $authPayload
            ));
                $responseResult = $asyncResponse->get();
                $responseData = $responseResult->getBody();
                $responseData = json_decode($responseData);
            if (isset($responseData->access_token)) {
                $result = $responseData->access_token;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve access token.'));
            }
            return $result;
        } catch (\Magento\Framework\HTTP\AsyncClient\HttpException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error occurred: %1', $e->getMessage()));
        }
    }
}
