<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model;

/**
 * Authorize.net debug payment method model
 *
 * @method string getRequestBody()
 * @method \Magento\Authorizenet\Model\Debug setRequestBody(string $value)
 * @method string getResponseBody()
 * @method \Magento\Authorizenet\Model\Debug setResponseBody(string $value)
 * @method string getRequestSerialized()
 * @method \Magento\Authorizenet\Model\Debug setRequestSerialized(string $value)
 * @method string getResultSerialized()
 * @method \Magento\Authorizenet\Model\Debug setResultSerialized(string $value)
 * @method string getRequestDump()
 * @method \Magento\Authorizenet\Model\Debug setRequestDump(string $value)
 * @method string getResultDump()
 * @method \Magento\Authorizenet\Model\Debug setResultDump(string $value)
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class Debug extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Construct debug class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorizenet\Model\ResourceModel\Debug::class);
    }
}
