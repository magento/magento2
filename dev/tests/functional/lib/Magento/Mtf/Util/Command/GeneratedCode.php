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
 * GeneratedCode removes generated code of Magento (like generated/code and generated/metadata).
 */
class GeneratedCode
{
    /**
     * Url to deleteMagentoGeneratedCode.php.
     */
    const URL = '/dev/tests/functional/utils/deleteMagentoGeneratedCode.php';

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
     * Remove generated code.
     *
     * @return void
     */
    public function delete()
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray(),
            CurlInterface::POST,
            []
        );
        $this->transport->read();
        $this->transport->close();
    }

    /**
     * Prepare parameter array.
     *
     * @return array
     */
    private function prepareParamArray()
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken())
        ];
    }
}
