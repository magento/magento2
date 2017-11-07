<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\ServiceInputProcessor;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for ConvertSpecialPrice plugin.
 */
class ConvertSpecialPriceTest extends TestCase
{
    /**
     * @dataProvider pluginIsRegisteredDataProvider
     * @param string $areaCode
     * @return void
     */
    public function testConvertSpecialPriceIsRegistered(string $areaCode)
    {
        /** @var State $appState */
        $appState = Bootstrap::getObjectManager()->get(State::class);
        $appState->setAreaCode($areaCode);
        $pluginInfo = Bootstrap::getObjectManager()->create(PluginList::class)
            ->get(ServiceInputProcessor::class, []);
        $this->assertSame(ConvertSpecialPrice::class, $pluginInfo['convert_special_price']['instance']);
    }

    public function pluginIsRegisteredDataProvider()
    {
        return [
            'rest' => [
                'area_code' => Area::AREA_WEBAPI_REST,
            ],
            'soap' => [
                'area_code' => Area::AREA_WEBAPI_SOAP,
            ],
        ];
    }

    /**
     * Test product special price will be converted back to string.
     *
     * @dataProvider aroundProcessDataProvider
     * @param string $areaCode
     * @param array $inputArray
     * @return void
     */
    public function testAroundProcess(string $areaCode, array $inputArray)
    {
        //Remove plugin from shared instances in order to reload it for specific area code.
        Bootstrap::getObjectManager()->removeSharedInstance(ConvertSpecialPrice::class);
        $configLoader = Bootstrap::getObjectManager()->get(ConfigLoaderInterface::class);
        $appState = Bootstrap::getObjectManager()->get(State::class);
        $appState->setAreaCode($areaCode);
        Bootstrap::getObjectManager()->configure($configLoader->load($areaCode));
        /** @var ServiceInputProcessor $inputProcessor */
        $inputProcessor = Bootstrap::getObjectManager()->get(ServiceInputProcessor::class);
        $inputData = $inputProcessor->process(ProductRepositoryInterface::class, 'save', $inputArray);
        $product = array_shift($inputData);
        self::assertSame('', $product->getCustomAttribute('special_price')->getValue());
    }

    /**
     * @return array
     */
    public function aroundProcessDataProvider()
    {
        return [
            'rest' => [
                'area_code' => Area::AREA_WEBAPI_REST,
                'input_array' => [
                    'product' => [
                        'sku' => 'simple',
                        'name' => 'Simple Product',
                        'visibility' => 4,
                        'type_id' => 'simple',
                        'price' => 3.62,
                        'status' => 1,
                        'attribute_set_id' => 4,
                        'custom_attributes' =>
                            [
                                [
                                    'attribute_code' => 'special_price',
                                    'value' => '',
                                ],
                            ],
                    ],
                    'sku' => 'simple',
                ],
            ],
            'soap' => [
                'area_code' => Area::AREA_WEBAPI_SOAP,
                'input_array' => [
                    'product' => [
                        'sku' => 'simple',
                        'name' => 'Simple Product',
                        'attributeSetId' => 4,
                        'price' => 3.62,
                        'status' => 1,
                        'visibility' => 4,
                        'typeId' => 'simple',
                        'customAttributes' =>
                            [
                                [
                                    'attributeCode' => 'special_price',
                                    'value' => '',
                                ],
                            ],
                    ],
                ],
            ],
        ];
    }
}
