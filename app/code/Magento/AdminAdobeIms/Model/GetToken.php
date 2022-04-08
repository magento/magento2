<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Represent the get user token functionality for AdminAdobeIMS
 */
class GetToken implements GetTokenInterface
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var TokenResponseInterfaceFactory
     */
    private TokenResponseInterfaceFactory $tokenResponseFactory;

    /**
     * @param ImsConfig $imsConfig
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param TokenResponseInterfaceFactory $tokenResponseFactory
     */
    public function __construct(
        ImsConfig $imsConfig,
        CurlFactory $curlFactory,
        Json $json,
        TokenResponseInterfaceFactory $tokenResponseFactory
    ) {
        $this->imsConfig = $imsConfig;
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
            $this->imsConfig->getTokenUrl(),
            [
                'grant_type' => 'authorization_code',
                'client_id' => $this->imsConfig->getAdminAdobeImsClientId(),
                'client_secret' => $this->imsConfig->getAdminAdobeImsClientSecret(),
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
