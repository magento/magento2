<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Config;


class XsdTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidXmlFile()
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/../_files/queue_invalid.xml");
        libxml_use_internal_errors(true);
        $result = $dom->schemaValidate(BP . "/lib/internal/Magento/Framework/Amqp/etc/queue.xsd");

        $errorsQty = count(libxml_get_errors());
        libxml_use_internal_errors(false);

        $this->assertFalse($result);
        $this->assertEquals(6, $errorsQty);
    }

    public function testValidXmlFile()
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/../_files/queue_valid.xml");
        libxml_use_internal_errors(true);
        $result = $dom->schemaValidate(BP . "/lib/internal/Magento/Framework/Amqp/etc/queue.xsd");

        $errorsQty = count(libxml_get_errors());
        libxml_use_internal_errors(false);

        $this->assertTrue($result);
        $this->assertEquals(0, $errorsQty);
    }
}
