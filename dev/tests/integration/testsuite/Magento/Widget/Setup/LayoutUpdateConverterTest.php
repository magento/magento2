<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Setup;

class LayoutUpdateConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LayoutUpdateConverter
     */
    private $converter;

    protected function setUp()
    {
        $this->converter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            LayoutUpdateConverter::class
        );
    }

    /**
     * @param string $value
     * @param string $expected
     * @dataProvider convertDataProvider
     */
    public function testConvert($value, $expected)
    {
        $this->assertEquals($expected, $this->converter->convert($value));
    }

    public function convertDataProvider()
    {
        // @codingStandardsIgnoreStart
        $beginning = '<body><referenceContainer name="content"><block class="Magento\CatalogWidget\Block\Product\ProductsList" name="23e38bbfa7cc6474454570e51aeffcc3" template="Magento_CatalogWidget::product/widget/content/grid.phtml"><action method="setData"><argument name="name" xsi:type="string">show_pager</argument><argument name="value" xsi:type="string">0</argument></action><action method="setData"><argument name="name" xsi:type="string">products_count</argument><argument name="value" xsi:type="string">10</argument></action><action method="setData">';
        $serializedWidgetXml = '<argument name="name" xsi:type="string">conditions_encoded</argument><argument name="value" xsi:type="string">a:3:[i:1;a:4:[s:4:`type`;s:50:`Magento|CatalogWidget|Model|Rule|Condition|Combine`;s:10:`aggregator`;s:3:`all`;s:5:`value`;s:1:`1`;s:9:`new_child`;s:0:``;]s:4:`1--1`;a:4:[s:4:`type`;s:50:`Magento|CatalogWidget|Model|Rule|Condition|Product`;s:9:`attribute`;s:3:`sku`;s:8:`operator`;s:2:`()`;s:5:`value`;s:15:`simple, simple1`;]s:4:`1--2`;a:4:[s:4:`type`;s:50:`Magento|CatalogWidget|Model|Rule|Condition|Product`;s:9:`attribute`;s:5:`price`;s:8:`operator`;s:2:`&lt;=`;s:5:`value`;s:2:`10`;]]</argument>';
        $jsonEncodedWidgetXml = '<argument name="name" xsi:type="string">conditions_encoded</argument><argument name="value" xsi:type="string">^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`()`,`value`:`simple, simple1`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`price`,`operator`:`^(=`,`value`:`10`^]^]</argument>';
        $ending = '</action><action method="setData"><argument name="name" xsi:type="string">page_var_name</argument><argument name="value" xsi:type="string">pobqks</argument></action></block></referenceContainer></body>';
        // @codingStandardsIgnoreEnd
        return [
            'no widget' => [
                $beginning . $ending,
                $beginning . $ending,
            ],
            'has serialized widget' => [
                $beginning . $serializedWidgetXml . $ending,
                $beginning . $jsonEncodedWidgetXml . $ending,
            ],
            'has json encoded widget' => [
                $beginning . $jsonEncodedWidgetXml . $ending,
                $beginning . $jsonEncodedWidgetXml . $ending,
            ],
        ];
    }
}
