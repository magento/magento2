<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;

/**
 * Class CreateCustomer.
 * Curl handler for creating customer through registration page.
 *
 */
class CreateCustomer extends Curl
{
    /**
     * Post request for creating customer
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData('fields');
        $fields = [];
        foreach ($data as $key => $field) {
            $fields[$key] = $field['value'];
        }
        $url = $_ENV['app_frontend_url'] . 'customer/account/createpost/?nocookie=true';
        $curl = new CurlTransport();
        $curl->write(CurlInterface::POST, $url, '1.0', [], $fields);
        $response = $curl->read();
        $curl->close();

        return $response;
    }
}
