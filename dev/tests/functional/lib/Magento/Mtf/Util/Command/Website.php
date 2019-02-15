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
 * Perform Website folder creation for functional tests executions.
 */
class Website
{
    /**
     * Url to website.php.
     */
    const URL = '/dev/tests/functional/utils/website.php';

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
     * Creates Website folder in root directory.
     *
     * @param string $websiteCode
     * @throws \Exception
     */
    public function create($websiteCode)
    {
        $this->transport->addOption(CURLOPT_HEADER, 1);
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray($websiteCode),
            CurlInterface::POST,
            []
        );
        $this->transport->read();
        $this->transport->close();
    }

    /**
     * Prepare parameter array.
     *
     * @param string $websiteCode
     * @return array
     */
    private function prepareParamArray($websiteCode)
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'website_code' => urlencode($websiteCode)
        ];
    }
}
