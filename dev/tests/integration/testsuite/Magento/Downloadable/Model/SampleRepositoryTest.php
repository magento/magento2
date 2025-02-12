<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;

/**
 * Test class for \Magento\Downloadable\Model\SampleRepository
 */
class SampleRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Downloadable\Model\Product\Type::class);
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesProvidedUrls()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $this->getTargetProduct(false);

        $links = $this->model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->model->getSamples($product);
        $this->assertEmpty($samples->getData());
        $downloadableSampleData = [
            'title' => 'Sample with URL resource',
            'sort_order' => 1,
            'sample_url' => 'http://www.sample.example.com/',
            'sample_type' => 'url',
        ];

        $sampleFactory = $this->objectManager->create(SampleInterfaceFactory::class);
        $extension = $product->getExtensionAttributes();
        $sample = $sampleFactory->create(['data' => $downloadableSampleData]);
        $sample->setStoreId($product->getStoreId());
        $sample->setSampleType($downloadableSampleData['sample_type']);
        $sample->setSampleUrl($downloadableSampleData['sample_url']);
        if (!$sample->getSortOrder()) {
            $sample->setSortOrder(1);
        }
        $extension->setDownloadableProductSamples([$sample]);
        $product->setExtensionAttributes($extension);
        $productRepository->save($product);

        $samples = $this->getTargetProduct(false)->getExtensionAttributes()->getDownloadableProductSamples();
        $sample = reset($samples);

        $this->assertNotEmpty($sample->getData());
        $this->assertCount(1, $samples);

        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sampleData = $sample->getData();
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        foreach ($downloadableSampleData as $key => $value) {
            $this->assertArrayHasKey($key, $sampleData);
            $this->assertEquals($value, $sampleData[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesTitleInStoreViewScope(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $this->getTargetProduct(false);

        $links = $this->model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->model->getSamples($product);
        $this->assertEmpty($samples->getData());
        $downloadableSampleData = [
            'title' => 'Store View Title',
            'sort_order' => 1,
            'sample_url' => 'http://www.sample.example.com/',
            'sample_type' => 'url'
        ];

        $sampleFactory = $this->objectManager->create(SampleInterfaceFactory::class);
        $extension = $product->getExtensionAttributes();
        $sample = $sampleFactory->create(['data' => $downloadableSampleData]);
        $sample->setStoreId($product->getStoreId());
        $sample->setSampleType($downloadableSampleData['sample_type']);
        $sample->setSampleUrl($downloadableSampleData['sample_url']);
        if (!$sample->getSortOrder()) {
            $sample->setSortOrder(1);
        }
        $extension->setDownloadableProductSamples([$sample]);
        $product->setExtensionAttributes($extension);
        $productRepository->save($product);

        $samples = $this->getTargetProduct(false)->getExtensionAttributes()->getDownloadableProductSamples();
        $sample = reset($samples);

        $this->assertNotEmpty($sample->getData());
        $this->assertCount(1, $samples);

        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sampleData = $sample->getData();
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        foreach ($downloadableSampleData as $key => $value) {
            $this->assertArrayHasKey($key, $sampleData);
            $this->assertEquals($value, $sampleData[$key]);
        }

        $globalScopeSample = $this->getTargetSample(
            $this->getTargetProduct(true),
            (int)$sampleData['sample_id']
        );
        $this->assertEmpty($globalScopeSample->getTitle());
    }

    /**
     * Retrieve product that was updated by test
     *
     * @param bool $isScopeGlobal if true product store ID will be set to 0
     * @return Product
     */
    private function getTargetProduct(bool $isScopeGlobal): Product
    {
        if ($isScopeGlobal) {
            $product = $this->objectManager->get(ProductFactory::class)
                ->create()->setStoreId(0)->load(1);
        } else {
            $product = $this->objectManager->get(ProductFactory::class)
                ->create()
                ->load(1);
        }

        return $product;
    }

    /**
     * Retrieve product sample by its ID (or first sample if ID is not specified)
     *
     * @param Product $product
     * @param int|null $sampleId
     * @return Sample|null
     */
    private function getTargetSample(Product $product, ?int $sampleId = null): ?Sample
    {
        $samples = $product->getExtensionAttributes()->getDownloadableProductSamples();
        if ($sampleId) {
            if ($samples) {
                foreach ($samples as $sample) {
                    if ((int)$sample->getId() === $sampleId) {
                        return $sample;
                    }
                }
            }

            return null;
        }

        return $samples[0];
    }
}
