<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Export;

/**
 * @magentoAppArea adminhtml
 */
class RowCustomizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\BundleImportExport\Model\Export\RowCustomizer
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\BundleImportExport\Model\Export\RowCustomizer::class
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
<<<<<<< HEAD
=======
     *
     * @return void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testPrepareData(): void
    {
        $parsedAdditionalAttributes = 'text_attribute=!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/'
            . ',text_attribute2=,';
        $allAdditionalAttributes = $parsedAdditionalAttributes . ',weight_type=0,price_type=1';
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $select = $collection->getConnection()->select()
            ->from(['p' => $collection->getTable('catalog_product_entity')], ['sku', 'entity_id'])
            ->where('sku IN(?)', ['simple', 'custom-design-simple-product', 'bundle-product']);
        $ids = $collection->getConnection()->fetchPairs($select);
        $select = (string)$collection->getSelect();
        $this->model->prepareData($collection, array_values($ids));
        $this->assertEquals($select, (string)$collection->getSelect());
        $result = $this->model->addData(['additional_attributes' => $allAdditionalAttributes], $ids['bundle-product']);
        $this->assertArrayHasKey('bundle_price_type', $result);
        $this->assertArrayHasKey('bundle_shipment_type', $result);
        $this->assertArrayHasKey('bundle_sku_type', $result);
        $this->assertArrayHasKey('bundle_price_view', $result);
        $this->assertArrayHasKey('bundle_weight_type', $result);
        $this->assertArrayHasKey('bundle_values', $result);
        $this->assertContains('sku=simple,', $result['bundle_values']);
        $this->assertEquals([], $this->model->addData([], $ids['simple']));
        $this->assertEquals($parsedAdditionalAttributes, $result['additional_attributes']);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
<<<<<<< HEAD
     */
    public function testPrepareDataWithDifferentStoreValues()
=======
     *
     * @return void
     */
    public function testPrepareDataWithDifferentStoreValues(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $storeCode = 'default';
        $expectedNames = [
            'name' => 'Bundle Product Items',
<<<<<<< HEAD
            'name_' . $storeCode => 'Bundle Product Items_' . $storeCode
=======
            'name_' . $storeCode => 'Bundle Product Items_' . $storeCode,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
        $parsedAdditionalAttributes = 'text_attribute=!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/'
            . ',text_attribute2=,';
        $allAdditionalAttributes = $parsedAdditionalAttributes . ',weight_type=0,price_type=1';
        $collection = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $store->load($storeCode, 'code');
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle-product', 1, $store->getId());

        $extension = $product->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();

        foreach ($options as $productOption) {
            $productOption->setTitle($productOption->getTitle() . '_' . $store->getCode());
        }
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $productRepository->save($product);
        $this->model->prepareData($collection, [$product->getId()]);
        $result = $this->model->addData(['additional_attributes' => $allAdditionalAttributes], $product->getId());
        $bundleValues = array_map(
            function ($input) {
                $data = explode('=', $input);
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                return [$data[0] => $data[1]];
            },
            explode(',', $result['bundle_values'])
        );
        $actualNames = [
            'name' => array_column($bundleValues, 'name')[0],
<<<<<<< HEAD
            'name' . '_' . $store->getCode() => array_column($bundleValues, 'name' . '_' . $store->getCode())[0]
        ];
=======
            'name' . '_' . $store->getCode() => array_column($bundleValues, 'name' . '_' . $store->getCode())[0],
        ];

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        self::assertSame($expectedNames, $actualNames);
    }
}
