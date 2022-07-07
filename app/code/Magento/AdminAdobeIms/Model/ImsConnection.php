<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Model\GetToken;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class ImsConnection
{
    private const HTTP_REDIRECT_CODE = 302;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var GetToken
     */
    private GetToken $token;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $adminAdobeImsLogger;

    /**
     * @param CurlFactory $curlFactory
     * @param ImsConfig $adminImsConfig
     * @param Json $json
     * @param GetToken $token
     * @param AdminAdobeImsLogger $adminAdobeImsLogger
     */
    public function __construct(
        CurlFactory $curlFactory,
        ImsConfig $adminImsConfig,
        Json $json,
        GetToken $token,
        AdminAdobeImsLogger $adminAdobeImsLogger
    ) {
        $this->curlFactory = $curlFactory;
        $this->adminImsConfig = $adminImsConfig;
        $this->json = $json;
        $this->token = $token;
        $this->adminAdobeImsLogger = $adminAdobeImsLogger;
    }

    /**
     * Get profile url
     *
     * @param string $code
     * @return array|bool|float|int|mixed|string|null
     * @throws AdobeImsAuthorizationException
     */
    public function getProfile(string $code)
    {
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');
        $curl->addHeader('Authorization', 'Bearer ' . $code);

        $curl->get($this->adminImsConfig->getProfileUrl());

        if ($curl->getBody() === '') {
            throw new AdobeImsAuthorizationException(
                __('Profile body is empty')
            );
        }

        return $this->json->unserialize($curl->getBody());
    }
}
