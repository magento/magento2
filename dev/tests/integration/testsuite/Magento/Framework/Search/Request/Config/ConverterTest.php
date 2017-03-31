<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Converter */
    protected $object;

    protected function setUp()
    {
        $this->object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Search\Request\Config\Converter::class);
    }

    public function testConvert()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '../../../_files/search_request.xml');
        $result = $this->object->convert($document);
        $expected = include __DIR__ . '/../../_files/search_request_config.php';
        $this->assertEquals($expected, $result);
    }
}
