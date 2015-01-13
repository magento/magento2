<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Handler\UrlRewrite;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create url rewrite
 */
class Curl extends AbstractCurl implements UrlRewriteInterface
{
    /**
     * Data mapping
     *
     * @var array
     */
    protected $mappingData = [
        'store_id' => [
            'Default Store View' => 1,
            'Main Website/Main Website Store/Default Store View' => 1,
        ],
        'redirect_type' => [
            'Temporary (302)' => 302,
            'Permanent (301)' => 301,
            'No' => 0,
        ],
    ];

    /**
     * Url for save rewrite
     *
     * @var string
     */
    protected $url = 'admin/url_rewrite/save/';

    /**
     * Post request for creating url rewrite
     *
     * @param FixtureInterface $fixture
     * @throws \Exception
     * @return void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url . $fixture->getTargetPath();
        $data = $this->replaceMappingData($fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("URL Rewrite creation by curl handler was not successful! Response: $response");
        }
        $curl->close();
    }
}
