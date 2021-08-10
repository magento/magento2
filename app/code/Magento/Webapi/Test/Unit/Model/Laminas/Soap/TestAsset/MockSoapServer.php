<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class MockSoapServer
{
    public $handle = null;
    public function handle()
    {
        $this->handle = func_get_args();
    }
    public function __call($name, $args)
    {
    }
}
