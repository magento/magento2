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
     */
    public function testPrepareData()
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
     */
    public function testPrepareDataWithDifferentStoreValues()
    {
        $storeCode = 'default';
        $expectedNames = [
            'name' => 'Bundle Product Items',
            'name_' . $storeCode => 'Bundle Product Items_' . $storeCode
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
                return [$data[0] => $data[1]];
            },
            explode(',', $result['bundle_values'])
        );
        $actualNames = [
            'name' => array_column($bundleValues, 'name')[0],
            'name' . '_' . $store->getCode() => array_column($bundleValues, 'name' . '_' . $store->getCode())[0]
        ];
        self::assertSame($expectedNames, $actualNames);
    }
}
