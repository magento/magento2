<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory;
use Magento\Downloadable\Model\Sample;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Api\Data\UserInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Model\Product\Type.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(Type::class);
        /** @var WriteInterface $mediaDirectory */
        $this->mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Delete specific data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testDeleteTypeSpecificData(): void
    {
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        /** @var Link $link */
        foreach ($links as $link) {
            $data = $link->getData();
            $data['title'] = 'UPDATED . ' . $data['title'];
            $downloadableData['link'][] = $data;
        }
        foreach ($samples as $sample) {
            $downloadableData['sample'][] = $sample->getData();
        }

        $product->setDownloadableData($downloadableData);
        $this->model->deleteTypeSpecificData($product);
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->load(1);

        $links = $this->model->getLinks($product);
        $this->assertEmpty($links);
        $samples = $this->model->getSamples($product);
        $this->assertEmpty($samples->getData());
    }

    /**
     * Save specific data
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    public function testSaveTypeSpecificData(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $product = Bootstrap::getObjectManager()->create(
            Product::class
        );
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        $i=0;
        foreach ($links as $link) {
            $i++;
            $linkData = $link->getData();
            $linkData['is_delete'] = 0;
            $linkData['type'] = Download::LINK_TYPE_FILE;
            $linkData['title'] = 'Updated downloadable link #' . $i;
            $downloadableData['link'][] = $linkData;
        }
        $i=0;
        foreach ($samples as $sample) {
            $i++;
            $sampleData = $sample->getData();
            $sampleData['is_delete'] = 0;
            $sampleData['type'] = Download::LINK_TYPE_FILE;
            $sampleData['title'] = 'Updated downloadable sample #' . $i;
            $downloadableData['sample'][] = $sampleData;
        }

        $product->setDownloadableData($downloadableData);
        $sampleFactory = $objectManager->create(SampleInterfaceFactory::class);
        $linkFactory = $objectManager->create(LinkInterfaceFactory::class);
        $extension = $product->getExtensionAttributes();
        $expectedLink = [
            'is_shareable' => '2',
            'link_type' => 'file',
            'link_file' => '/j/e/jellyfish_2_4.jpg',
            'number_of_downloads' => '15',
            'price' => 15,
            'sample_type' => 'file',
            'sort_order' => '1',
            'title' => 'Updated downloadable link #1',
        ];
        $links = [];
        foreach ($downloadableData['link'] as $linkData) {
            if (!$linkData || (isset($linkData['is_delete']) && (bool)$linkData['is_delete'])) {
                continue;
            } else {
                $link = $linkFactory->create(['data' => $linkData]);
                $link->setId($linkData['link_id']);
                if (isset($linkData['sample'])) {
                    $link->setSampleType($linkData['sample']['type']);
                    $link->setSampleFileData($linkData['sample']['file']);
                    $expectedLink['sample_file'] = $linkData['sample']['file'];
                    $link->setSampleUrl($linkData['sample']['url']);
                }
                $link->setLinkType($linkData['type']);
                $link->setStoreId($product->getStoreId());
                $link->setWebsiteId($product->getStore()->getWebsiteId());
                $link->setProductWebsiteIds($product->getWebsiteIds());
                if (!$link->getSortOrder()) {
                    $link->setSortOrder(1);
                }
                if (null === $link->getPrice()) {
                    $link->setPrice(0);
                }
                if ($link->getIsUnlimited()) {
                    $link->setNumberOfDownloads(0);
                }
                $links[] = $link;
            }
        }

        $extension->setDownloadableProductLinks($links);

        if (isset($downloadableData['sample']) && is_array($downloadableData['sample'])) {
            $samples = [];
            foreach ($downloadableData['sample'] as $sampleData) {
                if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
                    continue;
                } else {
                    $sample = $sampleFactory->create(['data' => $sampleData]);
                    $sample->setId($sampleData['sample_id']);
                    $sample->setStoreId($product->getStoreId());
                    $sample->setSampleType($sampleData['type']);
                    $sample->setSampleUrl($sampleData['sample_url']);
                    if (!$sample->getSortOrder()) {
                        $sample->setSortOrder(1);
                    }
                    $samples[] = $sample;
                }
            }
            $extension->setDownloadableProductSamples($samples);
        }
        $product->setExtensionAttributes($extension);

        if ($product->getLinksPurchasedSeparately()) {
            $product->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
        } else {
            $product->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
        }

        $product->save();
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->load(1);

        $links = $product->getExtensionAttributes()->getDownloadableProductLinks();

        $this->assertNotEmpty($links);
        $this->assertCount(1, $links);
        /** @var Link $link */
        $link = reset($links);
        foreach ($expectedLink as $key => $value) {
            $this->assertTrue($link->hasData($key), 'Key ' . $key . ' not exist!');
            $this->assertArrayHasKey($key, $link);
            $this->assertEquals($value, $link->getData($key));
        }

        $expectedSample = [
            'sample_type' => 'file',
            'sort_order' => '1',
            'title' => 'Updated downloadable sample #1',
        ];

        $samples = $product->getExtensionAttributes()->getDownloadableProductSamples();
        $sample = reset($samples);
        $this->assertNotEmpty($sample->getData());
        $this->assertCount(1, $samples);
        /** @var Sample $sample */
        $sample = $sample->getData();
        /** @var UserInterface $testAttribute */
        foreach ($expectedSample as $key => $value) {
            $this->assertArrayHasKey($key, $sample);
            $this->assertEquals($value, $sample[$key]);
        }
    }

    /**
     * Check product buy state
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     *
     * @return void
     */
    public function testCheckProductBuyState(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository =$this->objectManager->create(
            ProductRepositoryInterface::class
        );
        $product = $productRepository->get('downloadable-product');
        $product->setLinksPurchasedSeparately(false);
        $productRepository->save($product);
        /** @var Option $option */
        $option = $this->objectManager->create(
            Option::class,
            ['data' => ['code' => 'info_buyRequest', 'value' => '{"qty":23}']]
        );
        $option->setProduct($product);
        $product->setCustomOptions(['info_buyRequest' => $option]);

        $this->model->checkProductBuyState($product);
        $linksFactory = $this->objectManager->get(CollectionFactory::class);
        $allLinksIds = $linksFactory->create()->addProductToFilter($product->getEntityId())->getAllIds();
        $this->assertEquals(
            '{"qty":23,"links":["' . implode('","', $allLinksIds) . '"]}',
            $product->getCustomOption('info_buyRequest')->getValue()
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->mediaDirectory->delete('downloadable/files/');
    }
}
