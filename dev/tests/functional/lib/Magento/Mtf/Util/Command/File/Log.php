<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File;

use Magento\Mtf\Util\Protocol\CurlInterface;
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
     * Get file content.
     *
     * @param string $fileName
     * @return array
     */
    public function getFileContent($fileName)
    {
        $curl = $this->transport;
        $curl->write($this->prepareUrl($fileName), [], CurlInterface::GET);
        $data = $curl->read();
        $curl->close();

        return unserialize($data);
    }

    /**
     * Prepare url.
     *
     * @param string $fileName
     * @return string
     */
    private function prepareUrl($fileName)
    {
        return $_ENV['app_frontend_url'] . self::URL . '?fileName=' . urlencode($fileName);
    }
}
