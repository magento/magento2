<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Handler\Template;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Creation Newsletter Template
 */
class Curl extends AbstractCurl implements TemplateInterface
{
    /**
     * URL for newsletter template
     *
     * @var string
     */
    protected $url = 'newsletter/template/save/';

    /**
     * @param FixtureInterface $fixture [optional]
     * @throws \Exception
     * @return mixed|void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->replaceMappingData($fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Newsletter template creation by curl was not successful! Response: $response");
        }
        $curl->close();
    }
}
