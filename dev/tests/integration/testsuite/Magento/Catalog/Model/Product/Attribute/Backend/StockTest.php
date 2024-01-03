<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for backend stock attribute model.
 *
 * @see \Magento\Catalog\Model\Product\Attribute\Backend\Stock
 *
 * @magentoAppArea adminhtml
 */
class StockTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductInterfaceFactory */
    private $productFactory;

    /** @var Stock */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productFactory = $this->objectManager->get(ProductInterfaceFactory::class);
        $this->model = $this->objectManager->get(Stock::class);
        $this->model->setAttribute(
            $this->objectManager->get(Config::class)->getAttribute(Product::ENTITY, 'quantity_and_stock_status')
        );
    }

    /**
     * @return void
     */
    public function testValidate(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)__('Please enter a valid number in this field.'));
        $product = $this->productFactory->create();
        $product->setQuantityAndStockStatus(['qty' => 'string']);
        $this->model->validate($product);
    }
}
