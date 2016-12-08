<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type;

/**
 * Test for \Magento\Catalog\Model\Product\Option\Type\Date
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Type\Date
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Option\Type\Date::class
        );
    }

    /**
     * @covers       \Magento\Catalog\Model\Product\Option\Type\Date::prepareOptionValueForRequest()
     * @dataProvider prepareOptionValueForRequestDataProvider
     * @param array $optionValue
     * @param array $infoBuyRequest
     * @param array $expectedOptionValueForRequest
     * @param array $productOptionData
     */
    public function testPrepareOptionValueForRequest(
        array $optionValue,
        array $infoBuyRequest,
        array $productOptionData,
        array $expectedOptionValueForRequest
    ) {
        /** @var \Magento\Quote\Model\Quote\Item\Option $option */
        $option = $this->objectManager->create(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['data' => $infoBuyRequest]
        );
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $this->objectManager->create(\Magento\Quote\Model\Quote\Item::class);
        $item->addOption($option);
        /** @var \Magento\Catalog\Model\Product\Option|null $productOption */
        $productOption = $productOptionData
            ? $this->objectManager->create(
                \Magento\Catalog\Model\Product\Option::class,
                ['data' => $productOptionData]
            )
            : null;
        $this->model->setData('quote_item', $item);
        $this->model->setOption($productOption);

        $actualOptionValueForRequest = $this->model->prepareOptionValueForRequest($optionValue);
        $this->assertSame($expectedOptionValueForRequest, $actualOptionValueForRequest);
    }

    /**
     * @return array
     */
    public function prepareOptionValueForRequestDataProvider()
    {
        return [
            // Variation 1
            [
                // $optionValue
                ['field1' => 'value1', 'field2' => 'value2'],
                // $infoBuyRequest
                ['code' => 'info_buyRequest', 'value' => '{"qty":23}'],
                // $productOptionData
                ['id' => '11', 'value' => '{"qty":12}'],
                // $expectedOptionValueForRequest
                ['date_internal' => ['field1' => 'value1', 'field2' => 'value2']]
            ],
            // Variation 2
            [
                // $optionValue
                ['field1' => 'value1', 'field2' => 'value2'],
                // $infoBuyRequest
                ['code' => 'info_buyRequest', 'value' => '{"options":{"11":{"qty":23}}}'],
                // $productOptionData
                ['id' => '11', 'value' => '{"qty":12}'],
                // $expectedOptionValueForRequest
                ['qty' => 23]
            ],
            // Variation 3
            [
                // $optionValue
                ['field1' => 'value1', 'field2' => 'value2'],
                // $infoBuyRequest
                ['code' => 'info_buyRequest', 'value' => '{"options":{"11":{"qty":23}}}'],
                // $productOptionData
                [],
                // $expectedOptionValueForRequest
                ['date_internal' => ['field1' => 'value1', 'field2' => 'value2']]
            ],
        ];
    }
}
