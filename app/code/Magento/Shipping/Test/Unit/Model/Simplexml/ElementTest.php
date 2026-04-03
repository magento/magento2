<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Simplexml;

use Magento\Shipping\Model\Simplexml\Element;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    public function testXmlentities()
    {
        $xmlElement = new Element('<xml></xml>');
        $this->assertEquals('&amp;copy;&amp;', $xmlElement->xmlentities('&copy;&amp;'));
    }
}
