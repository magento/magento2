<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;

/**
 * GeneratedCode removes generated code of Magento (like generated/code and generated/metadata).
 */
class GeneratedCode
{
    /**
     * Url to deleteMagentoGeneratedCode.php.
     */
    const URL = 'dev/tests/functional/utils/deleteMagentoGeneratedCode.php';

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
     * Remove generated code.
     *
     * @return void
     */
    public function delete()
    {
        $url = $_ENV['app_frontend_url'] . self::URL;
        $curl = $this->transport;
        $curl->write($url, [], CurlInterface::GET);
        $curl->read();
        $curl->close();
    }
}
