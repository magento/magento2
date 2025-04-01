<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 18:54:53
 */

namespace Diepxuan\SyncCRM\Sync;

use Diepxuan\SyncCRM\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;

class CrmSync
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Context $context,
        Config $config
    ) {
        $this->curl   = $context->getCurl();
        $this->config = $config;
    }

    public function fetch($path = '')
    {
        $apiUrl   = $this->config->getApiUrl() . "/{$path}";
        $apiToken = $this->config->getApiToken();

        try {
            $this->curl->get($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
            ]);

            if (200 !== $this->curl->getStatus()) {
                return ['error' => 'Failed to fetch products. Status code: ' . $this->curl->getStatus()];
            }
            $response = $this->curl->getBody();
            if (empty($response)) {
                return ['error' => 'No data received from API.'];
            }
            if (!\is_string($response)) {
                return ['error' => 'Invalid response format.'];
            }
            if (false === json_decode($response)) {
                return ['error' => 'Invalid JSON response.'];
            }

            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
