<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleCatalogSearch\Model;

use Magento\TestFramework\Helper\Curl;

/**
 * Retrieve elasticsearch version by curl request
 */
class ElasticsearchVersionChecker
{
    /**
     * @var int
     */
    private $version;

    /**
     * @return int
     */
    public function getVersion() : int
    {
        if (!$this->version) {
            $curl = new Curl();
            $url = 'http://localhost:9200';
            $curl->get($url);
            $curl->addHeader('content-type', 'application/json');
            $data = $curl->getBody();
            $versionData = explode('.', json_decode($data, true)['version']['number']);
            $this->version = (int)array_shift($versionData);
        }

        return $this->version;
    }
}
