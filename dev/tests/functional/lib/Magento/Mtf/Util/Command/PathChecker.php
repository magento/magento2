<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * PathChecker checks that path to file or directory exists.
 */
class PathChecker
{
    /**
     * Url to checkPath.php.
     */
    const URL = '/dev/tests/functional/utils/pathChecker.php';

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
     * @constructor
     * @param CurlTransport $transport
     * @param WebapiDecorator $webapiHandler
     */
    public function __construct(CurlTransport $transport, WebapiDecorator $webapiHandler)
    {
        $this->transport = $transport;
        $this->webapiHandler = $webapiHandler;
    }

    /**
     * Check that $path exists.
     *
     * @param string $path
     * @return bool
     */
    public function pathExists($path)
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray($path),
            CurlInterface::POST,
            []
        );
        $result = $this->transport->read();
        $this->transport->close();
        return strpos($result, 'path exists: true') !== false;
    }

    /**
     * Prepare parameter array.
     *
     * @param string $path
     * @return array
     */
    private function prepareParamArray($path)
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'path' => urlencode($path)
        ];
    }
}
