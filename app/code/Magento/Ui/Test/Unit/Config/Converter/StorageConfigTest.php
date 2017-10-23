<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\StorageConfig;
use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\ConverterUtils;

class StorageConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StorageConfig
     */
    private $converter;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlConverter;

    public function setUp()
    {
        $this->urlConverter = $this->getMockBuilder(ConverterInterface::class)->getMockForAbstractClass();
        $this->converter = new StorageConfig($this->urlConverter, new ConverterUtils());
    }

    public function testConvert()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files/test.xml');
        $domXpath = new \DOMXPath($dom);
        $storageConfig = $domXpath->query('//listing/settings/storageConfig')->item(0);
        $path = $domXpath->query('//listing/settings/storageConfig/path')->item(0);
        $urlResult = [
            'name' => 'path',
            'xsi:type' => 'url',
            'path' => 'path',
        ];
        $this->urlConverter->expects($this->any())
            ->method('convert')
            ->with($path, ['type' => 'url'])
            ->willReturn($urlResult);
        $expectedResult = [
            'name' => 'storageConfig',
            'xsi:type' => 'array',
            'item' => [
                'provider' => [
                    'name' => 'provider',
                    'xsi:type' => 'string',
                    'value' => 'provider',
                ],
                'namespace' => [
                    'name' => 'namespace',
                    'xsi:type' => 'string',
                    'value' => 'namespace',
                ],
                'path' => $urlResult,
                'test' => [
                    'name' => 'test',
                    'xsi:type' => 'string',
                    'value' => 'test',
                ]
            ],
        ];
        $this->assertEquals($expectedResult, $this->converter->convert($storageConfig));
    }
}
