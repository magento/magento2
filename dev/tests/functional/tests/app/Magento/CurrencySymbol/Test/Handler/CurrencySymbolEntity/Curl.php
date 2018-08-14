<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Handler\CurrencySymbolEntity;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create Currency Symbol Entity
 */
class Curl extends AbstractCurl implements CurrencySymbolEntityInterface
{
    /**
     * Post request for creating currency symbol
     *
     * @param FixtureInterface $fixture
     * @return void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();
        $url = $_ENV['app_backend_url'] . 'admin/system_currencysymbol/save';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $curl->read();
        $curl->close();
        // Response verification is absent, because sending a post request returns an index page
    }
}
