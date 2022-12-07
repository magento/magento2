<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Provide IMS user profile
 */
class GetProfile implements GetProfileInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $imsConfig;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param ConfigInterface $imsConfig
     * @param CurlFactory $curlFactory
     * @param Json $json
     */
    public function __construct(
        ConfigInterface $imsConfig,
        CurlFactory $curlFactory,
        Json $json
    ) {
        $this->imsConfig = $imsConfig;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
    }

    /**
     * @inheritDoc
     */
    public function getProfile(string $code)
    {
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');
        $curl->addHeader('Authorization', 'Bearer ' . $code);

        $curl->get($this->imsConfig->getProfileUrl());

        if ($curl->getBody() === '') {
            throw new AuthorizationException(
                __('Profile body is empty')
            );
        }

        return $this->json->unserialize($curl->getBody());
    }
}
