<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Test cases for save category controller.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class SaveCategoryTest extends AbstractSaveCategoryTest
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var GetBlockByIdentifierInterface */
    private $getBlockByIdentifier;

    /** @var string */
    private $createdCategoryId;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CollectionFactory */
    private $categoryCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->getBlockByIdentifier = $this->_objectManager->get(GetBlockByIdentifierInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->categoryCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdCategoryId)) {
            try {
                $this->categoryRepository->deleteByIdentifier($this->createdCategoryId);
            } catch (NoSuchEntityException $e) {
                //Category already deleted.
            }
            $this->createdCategoryId = null;
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     *
     * @return void
     */
    public function testCreateCategoryWithCmsBlock(): void
    {
        $storeId = (int)$this->storeManager->getStore('default')->getId();
        $blockId = $this->getBlockByIdentifier->execute('fixture_block', $storeId)->getId();
        $postData = [
            CategoryInterface::KEY_NAME => 'Category with cms block',
            CategoryInterface::KEY_IS_ACTIVE => 1,
            CategoryInterface::KEY_INCLUDE_IN_MENU => 1,
            'display_mode' => Category::DM_MIXED,
            'landing_page' => $blockId,
            CategoryInterface::KEY_AVAILABLE_SORT_BY => ['position'],
            'default_sort_by' => 'position',
        ];
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);
        $this->createdCategoryId = $responseData['category']['entity_id'];
        $category = $this->categoryRepository->get($this->createdCategoryId);
        $this->assertEquals($blockId, $category->getLandingPage());
    }

    /**
     * @return void
     */
    public function testTryToCreateCategoryWithEmptyValues(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            CategoryInterface::KEY_NAME => 'test',
            CategoryInterface::KEY_IS_ACTIVE => 1,
            'use_config' => [],
            'return_session_messages_only' => false,
        ]);
        $this->dispatch('backend/catalog/category/save');
        $message = (string)__(
            'The "%1" attribute is required. Enter and try again.',
            'Available Product Listing Sort By'
        );
        $this->assertSessionMessages($this->containsEqual($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_root_category.php
     *
     * @return void
     */
    public function testSwitchingStoreViewsCategory(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $id = (int)$this->getCategoryIdByName('Second Root Category');
        $storeId = (int)$this->storeManager->getStore('default')->getId();
        $this->getRequest()->setParams(['store' => $storeId, 'id' => $id]);
        $this->dispatch('backend/catalog/category/save');
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
