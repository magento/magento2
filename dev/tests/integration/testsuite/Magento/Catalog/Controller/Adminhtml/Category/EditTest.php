<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test cases related to edit category.
 *
 * @see \Magento\Catalog\Controller\Adminhtml\Category\Edit
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class EditTest extends AbstractBackendController
{
    /** @var CollectionFactory */
    private $categoryCollectionFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_root_category.php
     *
     * @return void
     */
    public function testSwitchingStoreViewsCategory(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $id = (int)$this->getCategoryIdByName('Second Root Category');
        $storeId = (int)$this->storeManager->getStore('default')->getId();
        $this->getRequest()->setParams(['store' => $storeId, 'id' => $id]);
        $this->dispatch('backend/catalog/category/edit');
        $this->assertRedirect($this->stringContains('backend/catalog/category/index'));
        $this->assertStringNotContainsString('/id/', $this->getResponse()->getHeader('Location')->getFieldValue());
    }

    /**
     * Get category id by name
     *
     * @param string $name
     * @return string|null
     */
    private function getCategoryIdByName(string $name): ?string
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $category = $categoryCollection
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $name)
            ->setPageSize(1)
            ->getFirstItem();

        return $category->getId();
    }
}
