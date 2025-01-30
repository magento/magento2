<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;

/**
 * Test for customizable product option with "Date" type
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Date
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            Date::class
        );
    }

    /**
     * Check if option value for request is the same as expected
     *
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
        /** @var Option|null $productOption */
        $productOption = $productOptionData
            ? $this->objectManager->create(
                Option::class,
                ['data' => $productOptionData]
            )
            : null;
        $this->model->setData('quote_item', $item);
        $this->model->setOption($productOption);

        $actualOptionValueForRequest = $this->model->prepareOptionValueForRequest($optionValue);
        $this->assertSame($expectedOptionValueForRequest, $actualOptionValueForRequest);
    }

    /**
     * Data provider for testPrepareOptionValueForRequest
     *
     * @return array
     */
    public static function prepareOptionValueForRequestDataProvider()
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

    /**
     * Check date in prepareForCart method with javascript calendar and Asia/Singapore timezone
     *
     * @dataProvider testPrepareForCartDataProvider
     * @param array $dateData
     * @param array $productOptionData
     * @param array $requestData
     * @param string $expectedOptionValueForRequest
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     * @magentoConfigFixture current_store general/locale/timezone Asia/Singapore
     */
    public function testPrepareForCart(
        array $dateData,
        array $productOptionData,
        array $requestData,
        string $expectedOptionValueForRequest
    ) {
        $this->model->setData($dateData);
        /** @var Option|null $productOption */
        $productOption = $productOptionData
            ? $this->objectManager->create(
                Option::class,
                ['data' => $productOptionData]
            )
            : null;
        $this->model->setOption($productOption);
        $request = new DataObject();
        $request->setData($requestData);
        $this->model->setRequest($request);
        $actualOptionValueForRequest = $this->model->prepareForCart();
        $this->assertSame($expectedOptionValueForRequest, $actualOptionValueForRequest);
    }

    /**
     * Data provider for testPrepareForCart
     *
     * @return array
     */
    public static function testPrepareForCartDataProvider()
    {
        return [
            [
                // $dateData
                [
                    'is_valid' => true,
                    'user_value' => [
                        'date' => '09/30/2019',
                        'year' => 0,
                        'month' => 0,
                        'day' => 0,
                        'hour' => 0,
                        'minute' => 0,
                        'day_part' => '',
                        'date_internal' => ''
                    ]
                ],
                // $productOptionData
                ['id' => '11', 'value' => '{"qty":12}', 'type' => 'date'],
                // $requestData
                [
                    'options' => [
                        [
                            'date' => '09/30/2019'
                        ]
                    ]
                ],
                // $expectedOptionValueForRequest
                '2019-09-30 00:00:00'
            ]
        ];
    }
}
