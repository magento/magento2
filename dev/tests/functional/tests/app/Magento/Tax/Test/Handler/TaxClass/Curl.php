<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxClass;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating customer and product tax class
 */
class Curl extends AbstractCurl implements TaxClassInterface
{
    /**
     * Post request for creating tax class
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();

        $url = $_ENV['app_backend_url'] . 'tax/tax/ajaxSAve/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        $id = $this->getClassId($response);
        return ['id' => $id];
    }

    /**
     * Return saved class id if saved
     *
     * @param $response
     * @return int|null
     * @throws \Exception
     */
    protected function getClassId($response)
    {
        $data = json_decode($response, true);
        if ($data['success'] !== true) {
            throw new \Exception("Tax class creation by curl handler was not successful! Response: $response");
        }
        return isset($data['class_id']) ? (int)$data['class_id'] : null;
    }
}
