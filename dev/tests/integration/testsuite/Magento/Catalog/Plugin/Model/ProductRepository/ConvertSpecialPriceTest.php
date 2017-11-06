<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Webapi\Rest\Request;
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
     * @return void
     */
    public function testConvertSpecialPriceIsRegistered()
    {
        /** @var State $appState */
        $appState = Bootstrap::getObjectManager()->get(State::class);
        $appState->setAreaCode(Area::AREA_WEBAPI_REST);
        $pluginInfo = Bootstrap::getObjectManager()->create(PluginList::class)
            ->get(ProductRepositoryInterface::class, []);
        $this->assertSame(ConvertSpecialPrice::class, $pluginInfo['convert_special_price']['instance']);
    }

    /**
     * Test product special price will be converted back to string, if request is REST API.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @return void
     */
    public function testBeforeSave()
    {
        /** @var State $appState */
        $appState = Bootstrap::getObjectManager()->get(State::class);
        $appState->setAreaCode(Area::AREA_WEBAPI_REST);
        /** @var Request $request */
        $request = Bootstrap::getObjectManager()->get(Request::class);
        $bodyParams = new \ReflectionProperty(Request::class, '_bodyParams');
        $bodyParams->setAccessible(true);
        $bodyParams->setValue(
            $request,
            [
                'product' => [
                    'custom_attributes' =>
                        [
                            [
                                'attribute_code' => 'special_price',
                                'value' => '',
                            ],
                        ],
                ],
            ]
        );
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple');
        //Remove special_price from product data, because product which built during Api request doesn't have it.
        $product->unsetData('special_price');
        //Set custom attribute special_price to '0', same as in product which built during Api request.
        $product->setCustomAttribute('special_price', 0);
        $product = $productRepository->save($product);
        self::assertNull($product->getCustomAttribute('special_price'));
    }
}
