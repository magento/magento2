<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeSetSearchResults;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Integration test for product view front action.
 *
 * @magentoAppArea frontend
 */
class ViewTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var string
     */
    private $systemLogFileName = 'system.log';

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    /**
     * @var AttributeSetRepositoryInterface $attributeSetRepository
     */
    private $attributeSetRepository;

    /**
     * @var Type $productEntityType
     */
    private $productEntityType;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->attributeSetRepository = $this->_objectManager->create(AttributeSetRepositoryInterface::class);
        $this->productEntityType = $this->_objectManager->create(Type::class)
            ->loadByCode(Product::ENTITY);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     * @return void
     */
    public function testViewActionWithCanonicalTag(): void
    {
        $this->markTestSkipped(
            'MAGETWO-40724: Canonical url from tests sometimes does not equal canonical url from action'
        );
        $this->dispatch('catalog/product/view/id/1/');

        $this->assertContains(
            '<link  rel="canonical" href="http://localhost/index.php/catalog/product/view/_ignore_category/1/id/1/" />',
            $this->getResponse()->getBody()
        );
    }

    /**
     * View product with custom attribute when attribute removed from it.
     *
     * It tests that after changing product attribute set from Default to Custom
     * there are no warning messages in log in case Custom not contains attribute from Default.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_country_of_manufacture.php
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default_without_country_of_manufacture.php
     * @return void
     */
    public function testViewActionCustomAttributeSetWithoutCountryOfManufacture(): void
    {
        $product = $this->getProductBySku('simple_with_com');
        $attributeSetCustom = $this->getProductAttributeSetByName('custom_attribute_set_wout_com');

        $product->setAttributeSetId($attributeSetCustom->getAttributeSetId());
        $this->productRepository->save($product);

        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));
        $message = 'Attempt to load value of nonexistent EAV attribute';
        $this->assertFalse(
            $this->checkSystemLogForMessage($message),
            sprintf("Warning message found in %s: %s", $this->systemLogFileName, $message)
        );
    }

    /**
     * Check system log file for error message.
     *
     * @param string $message
     * @return bool
     */
    private function checkSystemLogForMessage(string $message): bool
    {
        $content = $this->getSystemLogContent();
        $pos = strpos($content, $message);

        return $pos !== false;
    }

    /**
     * Get product instance by sku.
     *
     * @param string $sku
     * @return Product
     */
    private function getProductBySku(string $sku): Product
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Get product attribute set by name.
     *
     * @param string $attributeSetName
     * @return Set|null
     */
    private function getProductAttributeSetByName(string $attributeSetName): ?Set
    {
        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->_objectManager->create(SortOrderBuilder::class);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('attribute_set_name', $attributeSetName);
        $searchCriteriaBuilder->addFilter('entity_type_id', $this->productEntityType->getId());
        $attributeSetIdSortOrder = $sortOrderBuilder
            ->setField('attribute_set_id')
            ->setDirection(Collection::SORT_ORDER_DESC)
            ->create();
        $searchCriteriaBuilder->addSortOrder($attributeSetIdSortOrder);
        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(1);

        /** @var AttributeSetSearchResults $searchResult */
        $searchResult = $this->attributeSetRepository->getList($searchCriteriaBuilder->create());
        $items = $searchResult->getItems();

        if (count($items) > 0) {
            return reset($items);
        }

        return null;
    }

    /**
     * Get system log content.
     *
     * @return string
     */
    private function getSystemLogContent(): string
    {
        $logDir = $this->getLogDirectoryWrite();
        $logFile = $logDir->openFile($this->systemLogFileName, 'rb');
        $content = $this->tail($logFile, 10);

        return $content;
    }

    /**
     * Get file tail.
     *
     * @param FileWriteInterface $file
     * @param int $lines
     * @param int $buffer
     * @return false|string
     */
    private function tail(FileWriteInterface $file, int $lines = 10, int $buffer = 4096)
    {
        // Jump to last character
        $file->seek(-1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if ($file->read(1) != "\n") {
            $lines--;
        }

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while ($file->tell() > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min($file->tell(), $buffer);

            // Do the jump (backwards, relative to where we are)
            $file->seek(-$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = $file->read($seek)) . $output;

            // Jump back to where we started reading
            $file->seek(-mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }

        // Close file and return
        $file->close();

        return $output;
    }

    /**
     * Get current LOG directory write.
     *
     * @return WriteInterface
     */
    private function getLogDirectoryWrite()
    {
        /** @var Filesystem $filesystem */
        $filesystem = $this->_objectManager->create(Filesystem::class);
        $logDirectory = $filesystem->getDirectoryWrite(DirectoryList::LOG);

        return $logDirectory;
    }
}
