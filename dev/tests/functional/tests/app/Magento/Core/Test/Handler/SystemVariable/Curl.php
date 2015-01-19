<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Handler\SystemVariable;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating custom system variable
 */
class Curl extends AbstractCurl implements SystemVariableInterface
{
    /**
     * Post request for creating custom system variable
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['variable'] = $fixture->getData();

        $url = $_ENV['app_backend_url'] . 'admin/system_variable/save/back/edit/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("System Variable creation by curl handler was not successful! Response: $response");
        }

        preg_match("~Location: [^\\s]*system_variable\\/edit\\/variable_id\\/(\\d+)~", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;
        return ['variable_id' => $id];
    }
}
