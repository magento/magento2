<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File;

use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Get content of log file in var/log folder.
 */
class Log
{
    /**
     * Url to log.php.
     */
    const URL = 'dev/tests/functional/utils/log.php';

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

    /**
     * Get content of log file in var/log folder by file name.
     *
     * @param string $name
     * @return array
     */
    public function getFileContent($name)
    {
        $curl = $this->transport;
        $curl->write($this->prepareUrl($name), [], CurlTransport::GET);
        $data = $curl->read();
        $curl->close();

        return unserialize($data);
    }

    /**
     * Prepare url.
     *
     * @param string $name
     * @return string
     */
    private function prepareUrl($name)
    {
        return $_ENV['app_frontend_url'] . self::URL . '?name=' . urlencode($name);
    }
}
