<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class errorClass
{
    public function triggerError()
    {
        trigger_error('TestError', E_USER_ERROR);
    }
}
