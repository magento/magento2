<?php

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AutoincrementUpdateTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ProductResource
     */
    private $productResource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var int
     */
    private $defaultAutoincrementStep = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testTableAutoincrementNotGrow(): void
    {
        //In case of different step value (by default = 1)
        $this->defaultAutoincrementStep = $this->getAutoincrementStep();
        $lockMode = $this->getAutoincrementLockMode();

        $productId = 10;
        $secondStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();

        $beforeTestValue = $this->getAutoincrementValueFor('catalog_product_entity_int');

        /** @var Product $product */
        $product = $this->objectManager->get(Product::class);
        $product->setId($productId);

        $this->productResource->load($product, $productId);

        //1. First save with no change. Should be the same
        //Make sure we override default value
        $product->setStoreId(0);
        $this->productResource->saveAttribute($product, 'status');

        $afterFirstSaveNoChangeValue = $this->getAutoincrementValueFor('catalog_product_entity_int');

        //2. Second save that adds one new value to other store
        $product
            ->setStoreId($secondStoreId)
            ->setStatus(Status::STATUS_DISABLED);

        $this->productResource->saveAttribute($product, 'status');

        $afterFirstSaveNewValue = $this->getAutoincrementValueFor('catalog_product_entity_int');

        /**
         * +1 needed to adjust for inserting in
         * @see \Magento\Catalog\Model\ResourceModel\AbstractResource::_insertAttribute
         * The status marked as is_required_in_admin_store in catalog_eav_attribute table
         */
        $expectedAutoincrementAfterFirstNewSave = $beforeTestValue + $this->defaultAutoincrementStep + 1;

        /*
         * 3. Save attribute a few more times with no changes
         * Starting from this point the autoincrement value will grow even though we do not change attribute values
         */
        $this->productResource->saveAttribute($product, 'status');
        $this->productResource->saveAttribute($product, 'status');
        $this->productResource->saveAttribute($product, 'status');
        $this->productResource->saveAttribute($product, 'status');
        $this->productResource->saveAttribute($product, 'status');
        $this->productResource->saveAttribute($product, 'status');

        $afterSeveralSavesWithNoChangeValue = $this->getAutoincrementValueFor('catalog_product_entity_int');

        //After additional saves the autoincrement value should not change
        $this->assertEquals($expectedAutoincrementAfterFirstNewSave, $afterSeveralSavesWithNoChangeValue);

        $this->assertEquals(
            $beforeTestValue,
            $afterFirstSaveNoChangeValue,
            sprintf(
                'Autoincrement value should not have changed. Before save: %s, After save with no change: %s',
                $beforeTestValue,
                $afterFirstSaveNoChangeValue
            )
        );

        $this->assertEquals(
            $expectedAutoincrementAfterFirstNewSave,
            $afterFirstSaveNewValue,
            sprintf(
                'Autoincrement value should have increased by %s. Increased by: %s',
                $this->defaultAutoincrementStep,
                ($afterFirstSaveNewValue - $beforeTestValue),
            )
        );

        $this->assertEquals(
            $afterFirstSaveNewValue,
            $afterSeveralSavesWithNoChangeValue,
            sprintf(
                'Autoincrement value should not have changed. Increased by: %s',
                ($afterSeveralSavesWithNoChangeValue - $afterFirstSaveNewValue),
            )
        );

        $this->assertEquals(
            0,
            $lockMode,
            'This test will pass only if (innodb_autoinc_lock_mode = 0)'
        );
    }

    /**
     * @param string $tableName
     * @return int
     */
    private function getAutoincrementValueFor(string $tableName): int
    {
        $connection = $this->productResource->getConnection();

        //Force update information_schema
        $connection->query(sprintf('ANALYZE TABLE `%s`;', $tableName))->execute();

        $select = $connection->select()
            ->from('INFORMATION_SCHEMA.TABLES', 'AUTO_INCREMENT')
            ->where('TABLE_NAME = (?)', $tableName)
            ->where('TABLE_SCHEMA = (SELECT DATABASE())');

        return (int)$connection->fetchOne($select);
    }

    /**
     * @return int
     */
    private function getAutoincrementLockMode(): int
    {
        $connection = $this->productResource->getConnection();
        $autoincrementLockMode = $connection->query("show variables where variable_name = 'innodb_autoinc_lock_mode';")->fetchAll();
        return (int) current($autoincrementLockMode)['Value'];
    }

    /**
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAutoincrementStep(): int
    {
        $connection = $this->productResource->getConnection();
        $value = $connection->query("show variables where variable_name = 'auto_increment_increment';")->fetchAll();
        return (int) current($value)['Value'];
    }
}
