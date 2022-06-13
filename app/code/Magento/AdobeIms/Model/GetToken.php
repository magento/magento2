<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Represent the get user token functionality
 */
class GetToken implements GetTokenInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var TokenResponseInterfaceFactory
     */
    private $tokenResponseFactory;

    /**
     * @param ConfigInterface $config
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param TokenResponseInterfaceFactory $tokenResponseFactory
     */
    public function __construct(
        ConfigInterface $config,
        CurlFactory $curlFactory,
        Json $json,
        TokenResponseInterfaceFactory $tokenResponseFactory
    ) {
        $this->config = $config;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->tokenResponseFactory = $tokenResponseFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $code): TokenResponseInterface
    {
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');

        $curl->post(
            $this->config->getTokenUrl(),
            [
                'grant_type' => 'authorization_code',
                'client_id' => $this->config->getApiKey(),
                'client_secret' => $this->config->getPrivateKey(),
                'code' => $code
            ]
        );

        $response = $this->json->unserialize($curl->getBody());

        if (!is_array($response) || empty($response['access_token'])) {
            throw new AuthorizationException(__('Could not login to Adobe IMS.'));
        }

        return $this->tokenResponseFactory->create(['data' => $response]);
    }
}
