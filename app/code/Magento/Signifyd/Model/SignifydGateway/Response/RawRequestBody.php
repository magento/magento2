<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

/**
 *  Reads raw data from the request body.
 */
class RawRequestBody
{
    /**
     * Returns raw data from the request body.
     *
     * @return string
     */
    public function get()
    {
        return file_get_contents("php://input") ?: '';
    }
}
