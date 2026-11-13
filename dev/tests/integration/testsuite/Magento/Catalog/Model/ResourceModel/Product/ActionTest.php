<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActionTest extends TestCase
{
    /**
     * @var Action
     */
    private Action $action;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var EavConfig
     */
    private EavConfig $eavConfig;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->action = $objectManager->create(Action::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->resource = $objectManager->get(ResourceConnection::class);
        $this->eavConfig = $objectManager->get(EavConfig::class);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(ProductFixture::class, as: 'product3'),
    ]
    public function testUpdateAttributesRemovesStoreViewValuesInSingleStoreMode(): void
    {
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->assertTrue($storeManager->hasSingleStore(), 'The test needs an install with one store view.');
        $storeId = (int)$storeManager->getDefaultStoreView()->getId();
        $linkField = $this->action->getLinkField();
        $updated = [
            (int)$this->fixtures->get('product1')->getData($linkField),
            (int)$this->fixtures->get('product2')->getData($linkField),
        ];
        $untouched = (int)$this->fixtures->get('product3')->getData($linkField);

        foreach (['status' => Status::STATUS_DISABLED, 'name' => 'Store view name'] as $code => $value) {
            foreach ([...$updated, $untouched] as $linkId) {
                $this->insertValue($code, $linkId, $storeId, $value);
            }
        }

        $profiler = $this->action->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        $this->action->updateAttributes(
            [
                (int)$this->fixtures->get('product1')->getId(),
                (int)$this->fixtures->get('product2')->getId(),
            ],
            ['status' => Status::STATUS_ENABLED, 'name' => 'Mass updated name'],
            Store::DEFAULT_STORE_ID
        );
        $deletes = $profiler->getQueryProfiles(\Zend_Db_Profiler::DELETE) ?: [];
        $profiler->setEnabled(false);
        $profiler->clear();

        $this->assertCount(2, $deletes, 'Expected one store view cleanup query per attribute.');
        foreach ($updated as $linkId) {
            $this->assertSame([], $this->fetchValues('status', $linkId, $storeId));
            $this->assertSame([], $this->fetchValues('name', $linkId, $storeId));
            $this->assertSame(
                [(string)Status::STATUS_ENABLED],
                $this->fetchValues('status', $linkId, Store::DEFAULT_STORE_ID)
            );
            $this->assertSame(['Mass updated name'], $this->fetchValues('name', $linkId, Store::DEFAULT_STORE_ID));
        }
        $this->assertSame([(string)Status::STATUS_DISABLED], $this->fetchValues('status', $untouched, $storeId));
        $this->assertSame(['Store view name'], $this->fetchValues('name', $untouched, $storeId));
    }

    /**
     * Writes a store view value directly, since a save in single store mode would store it on the default scope
     *
     * @param string $attributeCode
     * @param int $linkId
     * @param int $storeId
     * @param int|string $value
     * @return void
     */
    private function insertValue(string $attributeCode, int $linkId, int $storeId, int|string $value): void
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $this->resource->getConnection()->insertOnDuplicate(
            $attribute->getBackendTable(),
            [
                'attribute_id' => (int)$attribute->getId(),
                'store_id' => $storeId,
                $this->action->getLinkField() => $linkId,
                'value' => $value,
            ],
            ['value']
        );
    }

    /**
     * @param string $attributeCode
     * @param int $linkId
     * @param int $storeId
     * @return array
     */
    private function fetchValues(string $attributeCode, int $linkId, int $storeId): array
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($attribute->getBackendTable(), ['value'])
            ->where('attribute_id = ?', (int)$attribute->getId())
            ->where($this->action->getLinkField() . ' = ?', $linkId)
            ->where('store_id = ?', $storeId);

        return $connection->fetchCol($select);
    }
}
