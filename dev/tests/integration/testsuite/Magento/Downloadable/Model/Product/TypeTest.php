<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Downloadable\Model\Product\Type
 */
namespace Magento\Downloadable\Model\Product;

/**
 * Test for \Magento\Downloadable\Model\Product\Type
 */
class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(
            \Magento\Downloadable\Model\Product\Type::class
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     */
    public function testDeleteTypeSpecificData()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->_model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        /** @var \Magento\Downloadable\Model\Link $link */
        foreach ($links as $link) {
            $data = $link->getData();
            $data['title'] = 'UPDATED . ' . $data['title'];
            $downloadableData['link'][] = $data;
        }
        foreach ($samples as $sample) {
            $downloadableData['sample'][] = $sample->getData();
        }

        $product->setDownloadableData($downloadableData);
        $this->_model->deleteTypeSpecificData($product);
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);

        $links = $this->_model->getLinks($product);
        $this->assertEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertEmpty($samples->getData());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoAppArea adminhtml
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testSaveTypeSpecificData()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $product->setOrigData();
        $downloadableData = [];

        $links = $this->_model->getLinks($product);
        $this->assertNotEmpty($links);
        $samples = $this->_model->getSamples($product);
        $this->assertNotEmpty($samples->getData());
        $i=0;
        foreach ($links as $link) {
            $i++;
            $linkData = $link->getData();
            $linkData['is_delete'] = 0;
            $linkData['type'] = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            $linkData['title'] = 'Updated downloadable link #' . $i;
            $downloadableData['link'][] = $linkData;
        }
        $i=0;
        foreach ($samples as $sample) {
            $i++;
            $sampleData = $sample->getData();
            $sampleData['is_delete'] = 0;
            $sampleData['type'] = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            $sampleData['title'] = 'Updated downloadable sample #' . $i;
            $downloadableData['sample'][] = $sampleData;
        }

        $product->setDownloadableData($downloadableData);
        $sampleFactory = $objectManager->create(\Magento\Downloadable\Api\Data\SampleInterfaceFactory::class);
        $linkFactory = $objectManager->create(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
        $extension = $product->getExtensionAttributes();
        $expectedLink = [
            'is_shareable' => '2',
            'link_type' => 'file',
            'link_file' => '/j/e/jellyfish_2_4.jpg',
            'number_of_downloads' => '15',
            'price' => '15.000000',
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
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);

        $links = $product->getExtensionAttributes()->getDownloadableProductLinks();

        $this->assertNotEmpty($links);
        $this->assertCount(1, $links);
        /** @var \Magento\Downloadable\Model\Link $link */
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
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $sample->getData();
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        foreach ($expectedSample as $key => $value) {
            $this->assertArrayHasKey($key, $sample);
            $this->assertEquals($value, $sample[$key]);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @covers \Magento\Downloadable\Model\Product\Type::checkProductBuyState()
     */
    public function testCheckProductBuyState()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository =$this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $product = $productRepository->get('downloadable-product');
        $product->setLinksPurchasedSeparately(false);
        $productRepository->save($product);
        /** @var \Magento\Quote\Model\Quote\Item\Option $option */
        $option = $this->objectManager->create(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['data' => ['code' => 'info_buyRequest', 'value' => '{"qty":23}']]
        );
        $option->setProduct($product);
        $product->setCustomOptions(['info_buyRequest' => $option]);

        $this->_model->checkProductBuyState($product);
        $linksFactory = $this->objectManager
            ->get(\Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory::class);
        $allLinksIds = $linksFactory->create()->addProductToFilter($product->getEntityId())->getAllIds();
        $this->assertEquals(
            '{"qty":23,"links":["' . implode('","', $allLinksIds). '"]}',
            $product->getCustomOption('info_buyRequest')->getValue()
        );
    }
}
