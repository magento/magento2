<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\Converter;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\Converter
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\Converter
     */
    protected $converter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->converter = new Converter;
    }

    /**
     * @return void
     */
    public function testConvert()
    {
        $xmlFile = __DIR__ . '/_files/esconfig_test.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);

        $this->assertInternalType(
            'array',
            $result
        );
    }
}
