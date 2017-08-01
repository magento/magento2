<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model;

/**
 * @method \Magento\Authorizenet\Model\ResourceModel\Debug _getResource()
 * @method \Magento\Authorizenet\Model\ResourceModel\Debug getResource()
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
 * @since 2.0.0
 */
class Debug extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorizenet\Model\ResourceModel\Debug::class);
    }
}
