<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks category product index in cases when category unassigned from product
 *
 * @magentoDataFixture Magento/Catalog/_files/category_product_assigned_to_website.php
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class CategoryIndexTest extends AbstractBackendController
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductInterface */
    private $product;

    /** @var TableMaintainer */
    private $tableMaintainer;

    /** @var ProductResource */
    private $productResource;

    /** @var AdapterInterface */
    private $connection;

    /** @var DefaultCategory */
    private $defaultCategoryHelper;
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->product = $this->productRepository->get('product_with_category');
        $this->tableMaintainer = $this->_objectManager->create(TableMaintainer::class);
        $this->productResource = $this->_objectManager->get(ProductResource::class);
        $this->connection = $this->productResource->getConnection();
        $this->defaultCategoryHelper = $this->_objectManager->get(DefaultCategory::class);
    }

    /**
     * @return void
     */
    public function testUnassignCategory(): void
    {
        $postData = $this->preparePostData();
        $this->dispatchSaveProductRequest($postData);
        $this->assertEmpty($this->fetchIndexData());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product_assigned_to_website.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @return void
     */
    public function testReassignCategory(): void
    {
        $postData = $this->preparePostData(333);
        $this->dispatchSaveProductRequest($postData);
        $result = $this->fetchIndexData();
        $this->assertNotEmpty($result);
        $this->assertEquals(333, reset($result)['category_id']);
    }

    /**
     * Perform request
     *
     * @param array $postData
     * @return void
     */
    private function dispatchSaveProductRequest(array $postData): void
    {
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $this->product->getEntityId());
        $this->assertSessionMessages($this->equalTo(['You saved the product.']), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * Prepare data to request
     *
     * @param int|null $newCategoryId
     * @return array
     */
    private function preparePostData(?int $newCategoryId = null): array
    {
        $this->product->getWebsiteIds();
        $data = $this->product->getData();
        unset($data['entity_id'], $data['category_ids']);
        if ($newCategoryId) {
            $data['category_ids'] = [$newCategoryId];
        }

        return ['product' => $data];
    }

    /**
     * Fetch data from category product index table
     *
     * @return array
     */
    private function fetchIndexData(): array
    {
        $tableName = $this->tableMaintainer->getMainTable(Store::DISTRO_STORE_ID);
        $select = $this->connection->select();
        $select->from(['index_table' => $tableName], 'index_table.category_id')
            ->where('index_table.product_id = ?', $this->product->getId())
            ->where('index_table.category_id != ?', $this->defaultCategoryHelper->getId());

        return $this->connection->fetchAll($select);
    }
}
