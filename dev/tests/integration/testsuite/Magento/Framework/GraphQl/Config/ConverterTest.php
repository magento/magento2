<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\GraphQl\Config\Converter
     */
    private $graphQlConverter;

    /**
     * @var \DOMDocument
     */
    private $source;

    protected function setUp()
    {

        $this->graphQlConverter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\GraphQl\Config\Converter::class);
    }

    /**
     * @return void
     */
    public function testConvert()
    {
        $this->source= new \DOMDocument();
        $this->source->load(__DIR__ . '../../_files/input_graphql.xml');
        $actualOutput = $this->graphQlConverter->convert($this->source);
        $expectedResult = require __DIR__ . '../../_files/output_graphql.php';
        $this->assertEquals($expectedResult[0], $actualOutput);
    }
}
