<<<<<<< HEAD
<?php
=======
<?php declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

<<<<<<< HEAD
use Magento\Framework\ObjectManagerInterface;

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
/**
 * Tests Magento\Framework\Config\Convert
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @var Converter
     */
    private $converter;

    /**
     * Tests config value "false" is not interpreted as true.
     *
     * @param string $sourceString
     * @param array $expected
     * @dataProvider parseVarElementDataProvider
     */
    public function testParseVarElement($sourceString, $expected)
    {
        $document = new \DOMDocument();
        $document->loadXML($sourceString);
        $actual = $this->converter->convert($document);

        self::assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * Data provider for testParseVarElement.
     *
     * @return array
     */
    public function parseVarElementDataProvider()
    {
<<<<<<< HEAD
        // @codingStandardsIgnoreStart
        $sourceString = <<<'XML'
<?xml version="1.0"?>
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
=======
        $sourceString = <<<'XML'
<?xml version="1.0"?>
<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/view.xsd">
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    <vars module="Magento_Test">    
        <var name="str">some string</var>  
        <var name="int-1">1</var>        
        <var name="int-0">0</var>        
        <var name="bool-true">true</var> 
        <var name="bool-false">false</var> 
    </vars>
 </view>
XML;
<<<<<<< HEAD
        // @codingStandardsIgnoreEnd
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $expectedResult = [
            'vars' => [
                'Magento_Test' => [
                    'str' => 'some string',
                    'int-1' => '1',
                    'int-0' => '0',
                    'bool-true' => true,
                    'bool-false' => false
                ]
            ]
        ];

        return [
            [
                $sourceString,
                $expectedResult
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->converter = $this->objectManager->get(Converter::class);
=======
        $this->converter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
              ->create(\Magento\Framework\Config\Converter::class);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }
}
