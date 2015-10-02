<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class OrderItemGetTest extends WebapiAbstract
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
     * @magentoApiDataFixture Magento/Sales/_files/order_with_all_types_of_products.php
     */
    public function testOrderItemGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');
        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $item->getId(),
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                ],
                'soap' => [
                    'service' => self::SERVICE_READ_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_READ_NAME . 'get',
                ],
            ];
            $result = $this->_webApiCall($serviceInfo, ['id' => $item->getId()]);
            $this->assertEquals($item->getId(), $result['item_id']);
            if (isset($result['product_option']['extension_attributes']['custom_options'])
                && isset($item->getProductOptions()['info_buyRequest']['options'])
            ) {
                foreach ($item->getProductOptions()['info_buyRequest']['options'] as $itemOptionId => $itemOption) {
                    $this->assertOptions(
                        $result['product_option']['extension_attributes']['custom_options'],
                        $itemOption,
                        $itemOptionId
                    );
                }
            }
        }
    }

    /**
     * Check if option exists in item option
     *
     * @param array $resultOptions
     * @param array|string $option
     * @param null|string $optionId
     */
    protected function assertOptions(array $resultOptions, $option, $optionId = null)
    {
        $optionValid = false;
        if (is_array($option) && $optionId) {
            foreach ($resultOptions as $resultOption) {
                if ($resultOption['option_id'] == $optionId) {
                    $this->assertEquals(implode(',', $option), $resultOption['option_value']);
                    $optionValid = true;
                    break;
                }
            }
            $this->assertTrue($optionValid);
            return;
        }

        foreach ($resultOptions as $resultOption) {
            if ($resultOption['option_id'] == $optionId) {
                $this->assertEquals($resultOption['option_value'], $option);
                $optionValid = true;
                break;
            }
        }
        $this->assertTrue($optionValid);
    }
}
