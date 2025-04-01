<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 20:52:01
 */

namespace Diepxuan\SyncCRM\Sync;

use Diepxuan\SyncCRM\Helper\Config;
use Diepxuan\SyncCRM\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class CrmSync
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Context $context,
        Config $config
    ) {
        $this->curl   = $context->getCurl();
        $this->logger = $context->getLogger();
        $this->config = $config;
    }

    public function fetch($path = '')
    {
        $apiUrl   = $this->getConfig()->getApiUrl($path);
        $apiToken = $this->getConfig()->getApiToken();

        try {
            $this->getCurl()->setOption(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurl()->setHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]);
            $this->getCurl()->get($apiUrl);

            if (200 !== $this->getCurl()->getStatus()) {
                return ['error' => 'Failed to fetch products. Status code: ' . $this->getCurl()->getStatus()];
            }

            $response = $this->getCurl()->getBody();
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

    /**
     * @return Curl
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
