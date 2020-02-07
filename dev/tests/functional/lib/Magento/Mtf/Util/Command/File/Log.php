<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File;

use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Get content of log file in var/log folder.
 */
class Log
{
    /**
     * Url to log.php.
     */
    const URL = '/dev/tests/functional/utils/log.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * Webapi handler.
     *
     * @var WebapiDecorator
     */
    private $webapiHandler;

    /**
     * @param CurlTransport $transport
     * @param WebapiDecorator $webapiHandler
     */
    public function __construct(CurlTransport $transport, WebapiDecorator $webapiHandler)
    {
        $this->transport = $transport;
        $this->webapiHandler = $webapiHandler;
    }

    /**
     * Get content of log file in var/log folder by file name.
     *
     * @param string $name
     * @return array
     */
    public function getFileContent($name)
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray($name),
            CurlInterface::POST,
            []
        );
        $data = $this->transport->read();
        $this->transport->close();
        // phpcs:ignore Magento2.Security.InsecureFunction
        return unserialize($data);
    }

    /**
     * Prepare parameter array.
     *
     * @param string $name
     * @return array
     */
    private function prepareParamArray($name)
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'name' => urlencode($name)
        ];
    }
}
