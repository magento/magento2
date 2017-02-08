<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * PathChecker checks that path to file or directory exists.
 */
class PathChecker
{
    /**
     * Url to checkPath.php.
     */
    const URL = 'dev/tests/functional/utils/pathChecker.php';

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
     * Check that $path exists.
     *
     * @param string $path
     * @return bool
     */
    public function pathExists($path)
    {
        $url = $_ENV['app_frontend_url'] . self::URL . '?path=' . urlencode($path);
        $curl = $this->transport;
        $curl->write($url, [], CurlInterface::GET);
        $result = $curl->read();
        $curl->close();

        return strpos($result, 'path exists: true') !== false;
    }
}
