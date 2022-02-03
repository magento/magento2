<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\CurlFactory;

class Connection
{
    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        CurlFactory $curlFactory
    ) {
        $this->curlFactory = $curlFactory;
    }

    /**
     * @param string $clientId
     * @return bool
     * @throws InvalidArgumentException
     */
    public function testConnection(string $clientId): bool
    {
        $location = $this->auth($clientId);
        return $location !== '';
    }

    /**
     * @param string $clientId
     * @return string
     * @throws InvalidArgumentException
     */
    public function auth(string $clientId): string
    {
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');

//        $authUrl = $this->config->getAuthUrl();
        $authUrl = 'https://ims-na1-stg1.adobelogin.com/ims/authorize/v2';

        $curl->post(
            $authUrl,
            [
                'client_id' => $clientId,
//                'scope' => 'openid,additional_info.company,profile,role', // results in "invalid_scope"
                'scope' => 'openid',
                'response_type' => 'token',
                'redirect_uri' => 'https://adobe.loc'
            ]
        );

        if ($curl->getStatus() !== 302) {
            throw new InvalidArgumentException(__('Could not connect to Adobe IMS Service.'));
        }

        return $curl->getHeaders()['location'] ?? '';
    }
}

