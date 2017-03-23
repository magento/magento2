<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Handler\SystemVariable;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating custom system variable.
 */
class Curl extends AbstractCurl implements SystemVariableInterface
{
    /**
     * Post request for creating custom system variable.
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['variable'] = $fixture->getData();

        $url = $_ENV['app_backend_url'] . 'admin/system_variable/save/back/edit/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
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
