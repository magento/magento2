<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\Converter
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->converter = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\Config\Converter::class
        );
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
