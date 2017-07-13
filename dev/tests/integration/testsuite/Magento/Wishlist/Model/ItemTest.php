<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model;

/**
 * Item test class.
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Wishlist\Model\Item
     */
    private $model;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->model = $this->objectManager->get(\Magento\Wishlist\Model\Item::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testBuyRequest()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->getById(1);

        /** @var \Magento\Wishlist\Model\Item\Option $option */
        $option = $this->objectManager->create(
            \Magento\Wishlist\Model\Item\Option::class,
            ['data' => ['code' => 'info_buyRequest', 'value' => '{"qty":23}']]
        );
        $option->setProduct($product);
        $this->model->addOption($option);

        // Assert getBuyRequest method
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals($buyRequest->getOriginalQty(), 23);

        // Assert mergeBuyRequest method
        $this->model->mergeBuyRequest(['qty' => 11, 'additional_data' => 'some value']);
        $buyRequest = $this->model->getBuyRequest();
        $this->assertEquals(
            ['additional_data' => 'some value', 'qty' => 0, 'original_qty' => 11],
            $buyRequest->getData()
        );
    }

    public function testSetBuyRequest()
    {
        $buyRequest = $this->objectManager->create(
            \Magento\Framework\DataObject::class,
            ['data' => ['field_1' => 'some data', 'field_2' => 234]]
        );

        $this->model->setBuyRequest($buyRequest);

        $this->assertEquals(
            '{"field_1":"some data","field_2":234,"id":null}',
            $this->model->getData('buy_request')
        );
    }
}
