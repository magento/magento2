<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

class Locales
{
    /**
     * Url to locales.php.
     */
    const URL = 'dev/tests/functional/utils/locales.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * @param CurlTransport $transport
     */
    public function __construct(CurlTransport $transport)
    {
        $this->transport = $transport;
    }

    public function getAll()
    {
        return $this->getList('all');
    }

    public function getDeployed()
    {
        return $this->getList('deployed');
    }

    private function getList($type = 'all')
    {
        $url = $_ENV['app_frontend_url'] . self::URL . '?type' = $type;
        $curl = $this->transport;
        $curl->write($url, [], CurlInterface::GET);
        $result = $curl->read();
        $curl->close();

        return explode('|', $result);
    }
}
