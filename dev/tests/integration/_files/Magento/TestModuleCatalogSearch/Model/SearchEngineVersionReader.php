<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleCatalogSearch\Model;

use Magento\TestFramework\Helper\Curl;

/**
 * Retrieve search engine version by curl request
 */
class SearchEngineVersionReader
{
    /**
     * @var array
     */
    private $versionInfo;

    /**
     * Returns full search engine version e.g. 'elasticsearch7'
     *
     * @return string
     */
    public function getFullVersion(): string
    {
        $version = $this->getVersion();
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
            $url = 'http://localhost:9200';
            $curl->get($url);
            $curl->addHeader('content-type', 'application/json');
            $data = $curl->getBody();
            $this->versionInfo = json_decode($data, true);
        }

        return $this->versionInfo;
    }
}
