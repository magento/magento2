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
 * Perform bin/magento commands from command line for functional tests executions.
 */
class Cli
{
    /**
     * Url to command.php.
     */
    const URL = '/dev/tests/functional/utils/command.php';

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
     * Run command.
     *
     * @param string $command
     * @param array $options [optional]
     * @return void
     */
    public function execute($command, $options = [])
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray($command, $options),
            CurlInterface::POST,
            []
        );
        $this->transport->read();
        $this->transport->close();
    }

    /**
     * Prepare parameter array.
     *
     * @param string $command
     * @param array $options [optional]
     * @return array
     */
    private function prepareParamArray($command, $options = [])
    {
        if (!empty($options)) {
            $command .= ' ' . implode(' ', $options);
        }
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'command' => urlencode($command)
        ];
    }
}
