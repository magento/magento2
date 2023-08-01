<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use Magento\Framework\App\Cache\Type\Config as Cache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;

class UpsAuth
{
    public const TEST_AUTH_URL = 'https://wwwcie.ups.com/security/v1/oauth/token';
    public const CACHE_KEY_PREFIX = 'ups_api_token_';

    /**
     * @var AsyncClientInterface
     */
    private $asyncHttpClient;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param AsyncClientInterface|null $asyncHttpClient
     * @param Cache $cacheManager
     */
    public function __construct(AsyncClientInterface $asyncHttpClient = null, Cache $cacheManager)
    {
        $this->asyncHttpClient = $asyncHttpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
        $this->cache = $cacheManager;
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

        $cacheKey = self::CACHE_KEY_PREFIX;
        $result = $this->cache->load($cacheKey);
        if (!$result) {
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
                    $this->cache->save($result, $cacheKey, [], $responseData->expires_in ?: 10000);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve access token.'));
                }
                return $result;
            } catch (\Magento\Framework\HTTP\AsyncClient\HttpException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Error occurred: %1', $e->getMessage()));
            }
        }
        return $result;
    }
}
