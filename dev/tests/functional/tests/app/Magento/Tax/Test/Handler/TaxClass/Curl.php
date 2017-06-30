<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxClass;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating customer and product tax class.
 */
class Curl extends AbstractCurl implements TaxClassInterface
{
    /**
     * Post request for creating tax class.
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();

        $url = $_ENV['app_backend_url'] . 'tax/tax/ajaxSave/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        $id = $this->getClassId($response);
        return ['id' => $id];
    }

    /**
     * Return saved class id if saved.
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
