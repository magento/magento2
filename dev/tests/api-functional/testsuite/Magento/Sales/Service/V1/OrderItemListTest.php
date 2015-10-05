<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class OrderItemListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders/items';

    const SERVICE_READ_NAME = 'salesItemRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_custom_options.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_with_custom_options.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_with_custom_options.php
     * @magentoApiDataFixture Magento/Sales/_files/order_with_all_types_of_products.php
     */
    public function testOrderItemList()
    {
        $productsSku = ['simple', 'configurable', 'bundle-product', 'downloadable-product'];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');

        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(
            'Magento\Framework\Api\FilterBuilder'
        );
        $searchCriteriaBuilder->addFilters(
            [
                $filterBuilder
                    ->setField('order_id')
                    ->setValue($order->getId())
                    ->create(),
            ]
        );
        $requestData = ['criteria' => $searchCriteriaBuilder->create()->__toArray()];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getList',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertArrayHasKey('items', $result);
        $items = $result['items'];

        foreach ($productsSku as $sku) {
            $product = $this->productRepository->get($sku);
            $productAdded = false;
            foreach ($items as $item) {
                if ($item['product_id'] == $product->getId()) {
                    $productAdded = true;
                    if (isset($item['product_option']['extension_attributes']['custom_options'])) {
                        $options = $this->getOptions($product);
                        foreach ($options as $option) {
                            $this->assertOptions(
                                $option,
                                $item['product_option']['extension_attributes']['custom_options']
                            );
                        }
                    }
                }
            }
            $this->assertTrue($productAdded);
        }
    }

    /**
     * Check if option exists in item option
     *
     * @param array $option
     * @param array $itemOptions
     * @return void
     */
    protected function assertOptions(array $option, array $itemOptions)
    {
        $optionExists = false;
        foreach ($itemOptions as $itemOption) {
            if ($itemOption['option_id'] == $option['option_id']
                && $itemOption['option_value'] == $option['option_value']
            ) {
                $optionExists = true;
            }
        }
        $this->assertTrue($optionExists);
    }


    /**
     * Receive product options with values
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    protected function getOptions(ProductInterface $product)
    {
        $options = [];
        /** @var ProductCustomOptionInterface $option */
        foreach ($product->getOptions() as $option) {
            $options[] = [
                'option_id' => $option->getId(),
                'option_value' => $this->getOptionValue($option),
            ];
        }
        return $options;
    }

    /**
     * Receive option value based on option type
     *
     * @param ProductCustomOptionInterface $option
     * @return null|string
     */
    protected function getOptionValue(ProductCustomOptionInterface $option)
    {
        $returnValue = null;
        switch ($option->getType()) {
            case 'field':
                $returnValue = 'Test value';
                break;
            case 'date_time':
                $returnValue = '2015,9,9,2,2,am,';
                break;
            case 'drop_down':
                $returnValue = '3-1-select';
                break;
            case 'radio':
                $returnValue = '4-1-radio';
                break;
        }
        return $returnValue;
    }
}
