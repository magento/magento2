<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\Converter\Item;
use Magento\Ui\Config\ConverterUtils;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    private $converter;

    /**
     * @var \DOMXPath
     */
    private $domXpath;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlConverter;

    public function setUp()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files/test.xml');
        $this->domXpath = new \DOMXPath($dom);
        $this->urlConverter = $this->getMockBuilder(ConverterInterface::class)->getMockForAbstractClass();
        $this->converter = new Item($this->urlConverter, new ConverterUtils());
    }

    /**
     * @param array $expectedResult
     * @param string $xpath
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $expectedResult, $xpath)
    {
        $node = $this->domXpath->query($xpath)->item(0);
        if ($xpath == '//listing/columns/settings/editorConfig') {
            $urlNode = $this->domXpath->query($xpath . '/param[@name="clientConfig"]/item[@name="saveUrl"]')->item(0);
            $urlResult = [
                'name' => 'saveUrl',
                'xsi:type' => 'url',
                'path' => 'cms/page/inlineEdit',
            ];
            $this->urlConverter->expects($this->any())
                ->method('convert')
                ->with($urlNode, ['type' => 'url'])
                ->willReturn($urlResult);
            $expectedResult = array_replace_recursive(
                $expectedResult,
                ['name' => 'editorConfig',
                    'xsi:type' => 'array',
                    'item' => [
                        'clientConfig' => [
                            'name' => 'clientConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'saveUrl' => $urlResult,
                            ],
                        ],
                    ],
                ]
            );
        }
        $this->assertEquals($expectedResult, $this->converter->convert($node));
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            $this->getSetOne() + $this->getSetTwo() + $this->getSetThree()
        ];
    }

    /**
     * @return array
     */
    private function getSetOne()
    {
        return [
            [
                'name' => 'templates',
                'xsi:type' => 'array',
                'item' => [
                    'filters' => [
                        'name' => 'filters',
                        'xsi:type' => 'array',
                        'item' => [
                            'select' => [
                                'name' => 'select',
                                'xsi:type' => 'array',
                                'item' => [
                                    'template' => [
                                        'name' => 'template',
                                        'xsi:type' => 'string',
                                        'value' => 'ui/grid/filters/elements/ui-select',
                                    ],
                                    'component' => [
                                        'name' => 'component',
                                        'xsi:type' => 'string',
                                        'value' => 'Magento_Ui/js/form/element/ui-select',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '//listing/listingToolbar/filters/settings/templates'
        ];
    }

    /**
     * @return array
     */
    private function getSetTwo()
    {
        return [
            'editorConfig' => [
                'name' => 'editorConfig',
                'xsi:type' => 'array',
                'item' => [
                    'clientConfig' => [
                        'name' => 'clientConfig',
                        'xsi:type' => 'array',
                        'item' => [
                            'validateBeforeSave' => [
                                'name' => 'validateBeforeSave',
                                'xsi:type' => 'boolean',
                                'value' => 'false',
                            ],
                        ],
                    ],
                    'indexField' => [
                        'name' => 'indexField',
                        'xsi:type' => 'string',
                        'value' => 'page_id',
                    ],
                    'enabled' => [
                        'name' => 'enabled',
                        'xsi:type' => 'boolean',
                        'value' => 'true',
                    ],
                    'selectProvider' => [
                        'name' => 'selectProvider',
                        'xsi:type' => 'string',
                        'value' => 'cms_page_listing.cms_page_listing.cms_page_columns.ids',
                    ],
                ],
            ],
            '//listing/columns/settings/editorConfig'
        ];
    }

    /**
     * @return array
     */
    private function getSetThree()
    {
        return [
            'templates' => [
                'name' => 'templates',
                'xsi:type' => 'array',
                'item' => [
                    'filters' => [
                        'name' => 'filters',
                        'xsi:type' => 'array',
                        'item' => [
                            'select' => [
                                'name' => 'select',
                                'xsi:type' => 'array',
                                'item' => [
                                    'template' => [
                                        'name' => 'template',
                                        'xsi:type' => 'string',
                                        'value' => 'ui-select-template',
                                    ],
                                    'component' => [
                                        'name' => 'component',
                                        'xsi:type' => 'string',
                                        'value' => 'ui-select',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '//listing/filters/settings/templates'
        ];
    }
}
