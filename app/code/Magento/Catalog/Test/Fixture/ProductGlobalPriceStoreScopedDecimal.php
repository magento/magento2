<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Second store, store-scoped decimal attribute, and simple product for AC-40218 / PR 40419 coverage.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductGlobalPriceStoreScopedDecimal implements RevertibleDataFixtureInterface
{
    private const STORE_CODE = 'fixture_second_store';

    private const SKU = 'simple-global-price-store-decimal-pr40419';

    private const ATTRIBUTE_CODE = 'decimal_attr_store_scope_pr40419';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductFactory $productFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly EavConfig $eavConfig,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->ensureSecondStore();
        $objectManager = ObjectManager::getInstance();
        /** @var CategorySetup $installer */
        $installer = $objectManager->create(CategorySetup::class);
        $attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');

        $installer->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'decimal',
                'label' => 'Store scoped decimal PR40419',
                'input' => 'text',
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'user_defined' => true,
                'visible' => true,
                'group' => 'General',
            ]
        );

        $this->eavConfig->clear();

        $product = $this->productFactory->create();
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([$this->storeManager->getWebsite()->getId()])
            ->setName('Product global price and store scoped decimal')
            ->setSku(self::SKU)
            ->setPrice(77.5)
            ->setWeight(1)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(
                [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1,
                ]
            );
        $product->setCustomAttribute(self::ATTRIBUTE_CODE, '1.25');
        $this->productRepository->save($product);

        $secondStoreId = (int)$this->storeManager->getStore(self::STORE_CODE)->getId();
        $currentStoreCode = $this->storeManager->getStore()->getCode();

        try {
            $this->storeManager->setCurrentStore(self::STORE_CODE);
            $product = $this->productRepository->get(self::SKU, false, $secondStoreId, true);
            $product->setCustomAttribute(self::ATTRIBUTE_CODE, '9.99');
            $this->productRepository->save($product);
        } finally {
            $this->storeManager->setCurrentStore($currentStoreCode);
        }

        return new DataObject(
            [
                'sku' => self::SKU,
                'attribute_code' => self::ATTRIBUTE_CODE,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $sku = $data->getSku() ?: self::SKU;
        $attributeCode = $data->getAttributeCode() ?: self::ATTRIBUTE_CODE;

        try {
            $this->productRepository->deleteById($sku);
        } catch (NoSuchEntityException) {
        }

        try {
            $attribute = $this->attributeRepository->get($attributeCode);
            $this->attributeRepository->delete($attribute);
        } catch (NoSuchEntityException) {
        }

        $currentStoreCode = $this->storeManager->getStore()->getCode();
        try {
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
            $objectManager = ObjectManager::getInstance();
            /** @var Store $fixtureStore */
            $fixtureStore = $objectManager->create(Store::class);
            $fixtureStore->load(self::STORE_CODE, 'code');
            if ($fixtureStore->getId()) {
                $fixtureStore->delete();
            }
            $this->storeManager->reinitStores();
        } finally {
            $this->storeManager->setCurrentStore($currentStoreCode);
        }

        $this->eavConfig->clear();
    }

    private function ensureSecondStore(): void
    {
        $objectManager = ObjectManager::getInstance();
        /** @var Store $store */
        $store = $objectManager->create(Store::class);
        if (!$store->load(self::STORE_CODE, 'code')->getId()) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $groupId = $this->storeManager->getWebsite()->getDefaultGroupId();
            $store->setCode(self::STORE_CODE)
                ->setWebsiteId($websiteId)
                ->setGroupId($groupId)
                ->setName('Fixture Store')
                ->setSortOrder(10)
                ->setIsActive(1);
            $store->save();
            $this->storeManager->reinitStores();
        }
    }
}
