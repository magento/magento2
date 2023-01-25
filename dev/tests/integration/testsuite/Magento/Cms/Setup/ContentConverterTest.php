<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Setup;

class ContentConverterTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var \Magento\Cms\Setup\ContentConverter */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cms\Setup\ContentConverter::class
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
        $someContent = '<div class="content-heading">
   <h2 class="title">Hot Sellers</h2>
   <p class="info">Here is what`s trending on Luma right now</p>
</div>';
        $serializedWidgetContent = '{{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" products_per_page="8" products_count="8" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="a:2:[i:1;a:4:[s:4:`type`;s:50:`Magento|CatalogWidget|Model|Rule|Condition|Combine`;s:10:`aggregator`;s:3:`all`;s:5:`value`;s:1:`1`;s:9:`new_child`;s:0:``;]s:4:`1--1`;a:4:[s:4:`type`;s:50:`Magento|CatalogWidget|Model|Rule|Condition|Product`;s:9:`attribute`;s:3:`sku`;s:8:`operator`;s:2:`()`;s:5:`value`;a:8:[i:0;s:4:`WS12`;i:1;s:4:`WT09`;i:2;s:4:`MT07`;i:3;s:4:`MH07`;i:4;s:7:`24-MB02`;i:5;s:7:`24-WB04`;i:6;s:8:`241-MB08`;i:7;s:8:`240-LV05`;]]]"}}';
        $jsonEncodedWidgetContent = '{{widget type="Magento\\CatalogWidget\\Block\\Product\\ProductsList" products_per_page="8" products_count="8" template="Magento_CatalogWidget::product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`()`,`value`:[`WS12`,`WT09`,`MT07`,`MH07`,`24-MB02`,`24-WB04`,`241-MB08`,`240-LV05`]^]^]"}}';
        // @codingStandardsIgnoreEnd
        return [
            'no widget' => [
                $someContent,
                $someContent,
            ],
            'one serialized widget, end with widget' => [
                $someContent . $serializedWidgetContent,
                $someContent . $jsonEncodedWidgetContent,
            ],
            'two serialized widgets, end with widget' => [
                $someContent . $serializedWidgetContent . $someContent . $serializedWidgetContent,
                $someContent . $jsonEncodedWidgetContent . $someContent . $jsonEncodedWidgetContent,
            ],
            'one serialized widget, end with content other than widget' => [
                $someContent . $serializedWidgetContent . $someContent,
                $someContent . $jsonEncodedWidgetContent . $someContent,
            ],
            'one json encoded widget, end with widget' => [
                $someContent . $jsonEncodedWidgetContent,
                $someContent . $jsonEncodedWidgetContent,
            ],
            'two json encoded widgets, end with widget' => [
                $someContent . $jsonEncodedWidgetContent . $someContent . $jsonEncodedWidgetContent,
                $someContent . $jsonEncodedWidgetContent . $someContent . $jsonEncodedWidgetContent,
            ],
            'one json encoded widget, one serialized widget, end with widget' => [
                $someContent . $jsonEncodedWidgetContent . $someContent . $serializedWidgetContent,
                $someContent . $jsonEncodedWidgetContent . $someContent . $jsonEncodedWidgetContent,
            ],
            'one json encoded widget, end with content other than widget' => [
                $someContent . $jsonEncodedWidgetContent . $someContent,
                $someContent . $jsonEncodedWidgetContent . $someContent,
            ],
        ];
    }
}
