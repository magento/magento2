<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Simplexml;

class ElementTest extends \PHPUnit\Framework\TestCase
{
    public function testXmlentities()
    {
        $xmlElement = new \Magento\Shipping\Model\Simplexml\Element('<xml></xml>');
        $this->assertEquals('&amp;copy;&amp;', $xmlElement->xmlentities('&copy;&amp;'));
    }
}
