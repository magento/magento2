<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * @magentoAppArea adminhtml
 */
class AdvancedPricingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Expected Product Tier Price mapping with data
     *
     * @var array
     */
    protected $expectedTierPrice;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing');
        $this->expectedTierPrice = [
            'AdvancedPricingSimple 1' => [
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '300.0000',
                    'qty'               => '10.0000'
                ],
                [
                    'customer_group_id' => '1',
                    'value'             => '11.0000',
                    'qty'               => '11.0000'
                ],
                [
                    'customer_group_id' => '3',
                    'value'             => '14.0000',
                    'qty'               => '14.0000'
                ]
            ],
            'AdvancedPricingSimple 2' => [
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '1000000.0000',
                    'qty'               => '100.0000'
                ],
                [
                    'customer_group_id' => '0',
                    'value'             => '12.0000',
                    'qty'               => '12.0000'
                ],
                [
                    'customer_group_id' => '2',
                    'value'             => '13.0000',
                    'qty'               => '13.0000'
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/create_products.php
     * @magentoAppArea adminhtml
     */
    public function testAdvancedPricingImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/import_advanced_pricing.csv';
        $filesystem = $this->objectManager->create(
            'Magento\Framework\Filesystem'
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'advanced_pricing'
            ]
        )->validateData();

        $this->assertEquals(0, $errors->getErrorsCount(), 'Advanced pricing import validation error');
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $productIdList = $resource->getProductsIdsBySkus(array_keys($this->expectedTierPrice));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        foreach($productIdList as $sku => $productId) {
            $product->load($productId);
            $tierPriceCollection = $product->getTierPrices();
            $this->assertEquals(3, count($tierPriceCollection));
            /** @var \Magento\Catalog\Model\Product\TierPrice $tierPrice */
            foreach ($tierPriceCollection as $tierPrice) {
                $this->assertContains($tierPrice->getData(), $this->expectedTierPrice[$sku]);
            }
        }
    }
}
