<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Export;

/**
 * @magentoAppArea adminhtml
 */
class RowCustomizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableImportExport\Model\Export\RowCustomizer
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
            'Magento\ConfigurableImportExport\Model\Export\RowCustomizer'
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testPrepareData()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $productResource = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Product');
        $productId = $productResource->getIdBySku('configurable');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($productId);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Collection');
        /** @var \Magento\Framework\Model\Entity\MetadataPool $metadataPool */
        $metadataPool = $this->objectManager->get('Magento\Framework\Model\Entity\MetadataPool');
        /** @var \Magento\Framework\Model\Entity\EntityMetadata $productMetadata */
        $productMetadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $select = (string)$collection->getSelect();
        $this->model->prepareData($collection, [$product->getData($productMetadata->getLinkField()), 2, 3, 4]);
        $this->assertEquals($select, (string)$collection->getSelect());
        $result = $this->model->addData([], 1);
        $this->assertArrayHasKey('configurable_variations', $result);
        $this->assertArrayHasKey('configurable_variation_labels', $result);
        $this->assertEquals(
            'sku=simple_10,test_configurable=Option 1|sku=simple_20,test_configurable=Option 2',
            $result['configurable_variations']
        );
    }
}
