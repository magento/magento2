<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\App\Request\Http;

/**
 *  Reads raw data from the request body.
 */
class WebhookRequest
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Retrieve header value.
     *
     * @param string $name header name to retrieve.
     * @return string
     */
    public function getHeader($name)
    {
        return $this->request->getHeader($name) ?: '';
    }

    /**
     * Returns raw data from the request body.
     *
     * @return string
     */
    public function getBody()
    {
        return file_get_contents("php://input") ?: '';
    }
}
