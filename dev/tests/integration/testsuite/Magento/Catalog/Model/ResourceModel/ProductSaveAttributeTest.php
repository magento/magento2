<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Product::saveAttribute
 */
class ProductSaveAttributeTest extends TestCase
{
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'price' => 10]),
    ]
    public function testSaveAttributeUpdatesUpdatedAt(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $table = $resource->getTableName('catalog_product_entity');
        $productResource = $objectManager->get(Product::class);
        $product = $objectManager->get(ProductRepositoryInterface::class)->get('simple', true, 0, true);
        $linkField = $productResource->getLinkField();
        $linkValue = $product->getData($linkField);
        $oldDate = '2000-01-01 00:00:00';
        $connection->update($table, ['updated_at' => $oldDate], [$linkField . ' = ?' => $linkValue]);

        $product->setData('price', 25);
        $productResource->saveAttribute($product, 'price');

        $updatedAt = $connection->fetchOne(
            $connection->select()->from($table, ['updated_at'])->where($linkField . ' = ?', $linkValue)
        );
        $this->assertGreaterThan(strtotime($oldDate), strtotime($updatedAt));
    }
}
