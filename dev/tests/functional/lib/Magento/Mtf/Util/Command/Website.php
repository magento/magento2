<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Perform Website folder creation for functional tests executions.
 */
class Website
{
    /**
     * Url to website.php.
     */
    const URL = 'dev/tests/functional/utils/website.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * @constructor
     * @param CurlTransport $transport
     */
    public function __construct(CurlTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Creates Website folder in root directory.
     *
     * @param string $websiteCode
     * @throws \Exception
     */
    public function create($websiteCode)
    {
        $curl = $this->transport;
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($this->prepareUrl($websiteCode), [], CurlInterface::GET);
        $curl->read();
        $curl->close();
    }

    /**
     * Prepare url.
     *
     * @param string $websiteCode
     * @return string
     */
    private function prepareUrl($websiteCode)
    {
        return $_ENV['app_frontend_url'] . self::URL . '?website_code=' . urlencode($websiteCode);
    }
}
