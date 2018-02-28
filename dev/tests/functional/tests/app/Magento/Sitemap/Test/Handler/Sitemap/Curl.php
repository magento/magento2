<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Handler\Sitemap;

use Magento\Backend\Test\Handler\Extractor;
use Magento\Sitemap\Test\Handler\Sitemap;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating sitemap
 */
class Curl extends AbstractCurl implements SitemapInterface
{
    /**
     * Default attribute values for fixture
     *
     * @var array
     */
    protected $defaultAttributeValues = ['store_id' => 1];

    /**
     * Post request for creating sitemap
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'admin/sitemap/save/generate/';
        $data = array_merge($this->defaultAttributeValues, $fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Sitemap entity creating by curl handler was not successful! Response: $response");
        }

        return ['sitemap_id' => $this->getSitemapId($data)];
    }

    /**
     * Get id after created sitemap
     *
     * @param array $data
     * @return string|null
     */
    protected function getSitemapId(array $data)
    {
        //Sort data in grid to define sitemap id if more than 20 items in grid
        $url = 'admin/sitemap/index/sort/sitemap_id/dir/desc';
        $pattern = '/col\-sitemap_id[\s\W]*(\d+).*?' . $data['sitemap_filename'] . '/siu';
        $extractor = new Extractor($url, $pattern);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }
}
