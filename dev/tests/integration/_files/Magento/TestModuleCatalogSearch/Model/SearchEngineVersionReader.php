<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleCatalogSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Curl;

/**
 * Retrieve search engine version by curl request
 */
class SearchEngineVersionReader
{
    private const SEARCH_ENGINE_PATH = 'catalog/search/engine';

    /**
     * @var array
     */
    private $versionInfo;

    /**
     * Returns full search engine version e.g. 'elasticsearch8'
     *
     * @return string
     */
    public function getFullVersion(): string
    {
        $version = $this->getVersion();
        if (strtolower($this->getDistribution()) == 'opensearch') {
            $version = 1;
        }
        return $this->getDistribution() . ($version === 1 ? '' : $version);
    }

    /**
     * Returns search engine major version
     *
     * @return int
     */
    public function getVersion() : int
    {
        $info = $this->getInfo();
        $versionData = isset($info['version']['number']) ? explode('.', $info['version']['number']) : [];
        return (int) array_shift($versionData);
    }

    /**
     * Returns distribution name, e.g. 'opensearch'
     *
     * @return string
     */
    public function getDistribution() : string
    {
        $info = $this->getInfo();
        return $info['version']['distribution'] ?? 'elasticsearch';
    }

    /**
     * Retrieve main info about search engine
     *
     * @return array
     */
    private function getInfo(): array
    {
        if (!$this->versionInfo) {
            $curl = new Curl();

            $scopeConfig = Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);
            if ($scopeConfig->getValue(self::SEARCH_ENGINE_PATH)) {
                $engine = $scopeConfig->getValue(self::SEARCH_ENGINE_PATH);
                $serverHost = $scopeConfig->getValue("catalog/search/{$engine}_server_hostname");
                $port = $scopeConfig->getValue("catalog/search/{$engine}_server_port");
                $url = $serverHost . ':' . $port;
            } else {
                $url = 'http://localhost:9200';
            }

            $curl->get($url);
            $curl->addHeader('content-type', 'application/json');
            $data = $curl->getBody();
            $this->versionInfo = json_decode($data, true);
        }

        return $this->versionInfo;
    }
}
