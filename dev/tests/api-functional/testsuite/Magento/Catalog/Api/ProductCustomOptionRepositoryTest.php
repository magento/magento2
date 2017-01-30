<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductCustomOptionRepositoryTest extends WebapiAbstract
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    const SERVICE_NAME = 'catalogProductCustomOptionRepositoryV1';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productFactory = $this->objectManager->get('Magento\Catalog\Model\ProductFactory');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoAppIsolation enabled
     */
    public function testRemove()
    {
        $sku = 'simple';
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $customOptions = $product->getOptions();
        $optionId = array_pop($customOptions)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/products/$sku/options/$optionId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'DeleteByIdentifier',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, ['sku' => $sku, 'optionId' => $optionId]));
        /** @var  \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $this->assertNull($product->getOptionById($optionId));
        $this->assertEquals(9, count($product->getOptions()));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoAppIsolation enabled
     */
    public function testGet()
    {
        $productSku = 'simple';
        /** @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $service */
        $service = Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Api\ProductCustomOptionRepositoryInterface');
        $options = $service->getList('simple');
        $option = current($options);
        $optionId = $option->getOptionId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . "/options/" . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $option = $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'optionId' => $optionId]);
        unset($option['product_sku']);
        unset($option['option_id']);
        $excepted = include '_files/product_options.php';
        $this->assertEquals($excepted[0], $option);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoAppIsolation enabled
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetList()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . "/options",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $options = $this->_webApiCall($serviceInfo, ['sku' => $productSku]);

        /** Unset dynamic data */
        foreach ($options as $key => $value) {
            unset($options[$key]['product_sku']);
            unset($options[$key]['option_id']);
            if (!empty($options[$key]['values'])) {
                foreach ($options[$key]['values'] as $newKey => $valueData) {
                    unset($options[$key]['values'][$newKey]['option_type_id']);
                }
            }
        }

        $excepted = include '_files/product_options.php';
        $this->assertEquals(count($excepted), count($options));

        //in order to make assertion result readable we need to check each element separately
        foreach ($excepted as $index => $value) {
            $this->assertEquals($value, $options[$index]);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoAppIsolation enabled
     * @dataProvider optionDataProvider
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testSave($optionData)
    {
        $productSku = 'simple';

        $optionDataPost = $optionData;
        $optionDataPost['product_sku'] = $productSku;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['option' => $optionDataPost]);
        unset($result['product_sku']);
        unset($result['option_id']);
        if (!empty($result['values'])) {
            foreach ($result['values'] as $key => $value) {
                unset($result['values'][$key]['option_type_id']);
            }
        }
        $this->assertEquals($optionData, $result);
    }

    public function optionDataProvider()
    {
        $fixtureOptions = [];
        $fixture = include '_files/product_options.php';
        foreach ($fixture as $item) {
            $fixtureOptions[$item['type']] = [
                'optionData' => $item,
            ];
        };

        return $fixtureOptions;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoAppIsolation enabled
     * @dataProvider optionNegativeDataProvider
     */
    public function testAddNegative($optionData)
    {
        $productSku = 'simple';
        $optionDataPost = $optionData;
        $optionDataPost['product_sku'] = $productSku;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/products/options",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->setExpectedException('SoapFault', 'Could not save product option');
        } else {
            $this->setExpectedException('Exception', '', 400);
        }
        $this->_webApiCall($serviceInfo, ['option' => $optionDataPost]);
    }

    public function optionNegativeDataProvider()
    {
        $fixtureOptions = [];
        $fixture = include '_files/product_options_negative.php';
        foreach ($fixture as $key => $item) {
            $fixtureOptions[$key] = [
                'optionData' => $item,
            ];
        };

        return $fixtureOptions;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoAppIsolation enabled
     */
    public function testUpdate()
    {
        $productSku = 'simple';
        /** @var \Magento\Catalog\Model\ProductRepository $optionReadService */
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Model\ProductRepository'
        );

        $options = $productRepository->get($productSku, true)->getOptions();
        $option = array_shift($options);
        $optionId = $option->getOptionId();
        $optionDataPost = [
            'product_sku' => $productSku,
            'title' => $option->getTitle() . "_updated",
            'type' => $option->getType(),
            'sort_order' => $option->getSortOrder(),
            'is_require' => $option->getIsRequire(),
            'price' => $option->getPrice(),
            'price_type' => $option->getPriceType(),
            'sku' => $option->getSku(),
            'max_characters' => 500,
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/options/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $optionDataPost['option_id'] = $optionId;
            $updatedOption = $this->_webApiCall(
                $serviceInfo,
                ['id' => $optionId, 'option' => $optionDataPost]
            );
        } else {
            $updatedOption = $this->_webApiCall($serviceInfo, ['option' => $optionDataPost]);
        }

        unset($updatedOption['values']);
        $optionDataPost['option_id'] = $option->getOptionId();
        $this->assertEquals($optionDataPost, $updatedOption);
    }

    /**
     * @param string $optionType
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoAppIsolation enabled
     * @dataProvider validOptionDataProvider
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testUpdateOptionAddingNewValue($optionType)
    {
        $productId = 1;
        $fixtureOption = null;
        $valueData = [
            'price' => 100500,
            'price_type' => 'fixed',
            'sku' => 'new option sku ' . $optionType,
            'title' => 'New Option Title',
            'sort_order' => 100,
        ];

        $product = $this->productFactory->create();
        $product->load($productId);

        /**@var $option \Magento\Catalog\Model\Product\Option */
        foreach ($product->getOptions() as $option) {
            if ($option->getType() == $optionType) {
                $fixtureOption = $option;
                break;
            }
        }

        $values = [];
        foreach ($option->getValues() as $key => $value) {
            $values[] =
                [
                    'price' => $value->getPrice(),
                    'price_type' => $value->getPriceType(),
                    'sku' => $value->getSku(),
                    'title' => $value->getTitle(),
                    'sort_order' => $value->getSortOrder(),
                ];
        }
        $values[] = $valueData;
        $data = [
            'product_sku' => $option->getProductSku(),
            'title' => $option->getTitle(),
            'type' => $option->getType(),
            'is_require' => $option->getIsRequire(),
            'sort_order' => $option->getSortOrder(),
            'values' => $values,
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/options/' . $fixtureOption->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $data['option_id'] = $fixtureOption->getId();
            $valueObject = $this->_webApiCall(
                $serviceInfo,
                [ 'option_id' => $fixtureOption->getId(), 'option' => $data]
            );
        } else {
            $valueObject = $this->_webApiCall($serviceInfo, ['option' => $data]);
        }

        $values = end($valueObject['values']);
        $this->assertEquals($valueData['price'], $values['price']);
        $this->assertEquals($valueData['price_type'], $values['price_type']);
        $this->assertEquals($valueData['sku'], $values['sku']);
        $this->assertEquals('New Option Title', $values['title']);
        $this->assertEquals(100, $values['sort_order']);
    }

    public function validOptionDataProvider()
    {
        return [
            'drop_down' => ['drop_down'],
            'checkbox' => ['checkbox'],
            'radio' => ['radio'],
            'multiple' => ['multiple']
        ];
    }
}
