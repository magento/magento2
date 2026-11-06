<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[DbIsolation(false)]
#[DataFixture(StoreFixture::class, as: 'store_a')]
#[DataFixture(StoreFixture::class, as: 'store_b')]
#[DataFixture(
    AttributeFixture::class,
    [
        'default_frontend_label' => 'Default label',
        'frontend_labels' => [
            ['store_id' => '$store_a.id$', 'label' => 'Store A label'],
            ['store_id' => '$store_b.id$', 'label' => 'Store B label'],
        ],
    ],
    'attribute'
)]
#[DataFixture(ConfigurableProductFixture::class, ['_options' => ['$attribute$']], 'product')]
class StoreLabelTest extends TestCase
{
    public function testExplicitStoreLabelOverridesCollectionLabel(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $attribute = $this->loadAttributeWithStoreALabel();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $originalStore = $storeManager->getStore();
        $storeManager->setCurrentStore($fixtures->get('store_a')->getId());
        try {
            $this->assertSame('Store A label', $attribute->getData('store_label'));
            $this->assertSame('Store B label', $attribute->getStoreLabel($fixtures->get('store_b')->getId()));
            $this->assertSame('Store A label', $attribute->getStoreLabel($fixtures->get('store_a')->getId()));
            $this->assertSame('Default label', $attribute->getStoreLabel(0));
            $this->assertSame('Store A label', $attribute->getStoreLabel());
        } finally {
            $storeManager->setCurrentStore($originalStore);
        }
    }

    public function testSelectedAttributesInfoUsesProductStoreLabel(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $attribute = $this->loadAttributeWithStoreALabel();
        $product = $objectManager->get(ProductRepositoryInterface::class)->get(
            $fixtures->get('product')->getSku(),
            false,
            $fixtures->get('store_b')->getId(),
            true
        );
        $type = $product->getTypeInstance();
        foreach ($type->getConfigurableAttributes($product) as $configurableAttribute) {
            $configurableAttribute->setProductAttribute($attribute);
        }
        $optionId = $fixtures->get('attribute')->getData('option_1');
        $product->addCustomOption(
            'attributes',
            $objectManager->get(SerializerInterface::class)->serialize([$attribute->getId() => $optionId])
        );

        $info = $type->getSelectedAttributesInfo($product);

        $this->assertCount(1, $info);
        $this->assertSame('Store B label', $info[0]['label']);
        $this->assertSame('option_1', $info[0]['value']);
        $this->assertEquals($attribute->getId(), $info[0]['option_id']);
        $this->assertEquals($optionId, $info[0]['option_value']);
        $this->assertSame('Store A label', $attribute->getData('store_label'));
    }

    private function loadAttributeWithStoreALabel(): Attribute
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addFieldToFilter('main_table.attribute_id', $fixtures->get('attribute')->getId());
        $collection->addStoreLabel($fixtures->get('store_a')->getId());

        return $collection->getFirstItem();
    }
}
