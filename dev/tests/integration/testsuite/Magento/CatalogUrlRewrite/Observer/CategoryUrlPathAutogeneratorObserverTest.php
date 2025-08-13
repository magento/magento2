<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Framework\App\Response\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Website;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class CategoryUrlPathAutogeneratorObserverTest extends AbstractController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryFactory = $this->objectManager->get(CategoryFactory::class);
        $this->categoryCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DbIsolation(true),
        AppIsolation(true),
        DataFixtureBeforeTransaction(Website::class, as: 'website2'),
        DataFixtureBeforeTransaction(Group::class, ['website_id' => '$website2.id$'], as:'group2'),
        DataFixtureBeforeTransaction(
            Store::class,
            ['website_id' => '$website2.id$', 'group_id' => '$group2.id$'],
            as:'store2'
        ),
        DataFixture(CategoryFixture::class, ['url_key' => 'default-store-category1'], as:'category1')
    ]
    public function testChildrenUrlPathContainsParentCustomScopeUrlKey()
    {
        $category1 = $this->fixtures->get('category1');
        $secondStore = $this->fixtures->get('store2');

        $this->storeManager->setCurrentStore($secondStore);

        $secondStoreCategory1 = $this->categoryRepository->get($category1->getId(), $secondStore->getId());
        $secondStoreCategory1->setUrlKey('second-store-category-'.$category1->getId());
        $this->categoryRepository->save($secondStoreCategory1);

        $this->storeManager->setCurrentStore(StoreModel::DEFAULT_STORE_ID);

        $categoryData2 = $this->categoryFactory->create()->setData(
            [
                'parent_id' => $category1->getId(),
                'name' => 'Category 2',
                'url_key' => 'category-2',
                'is_active' => true
            ]
        );
        $category2 = $this->categoryRepository->save($categoryData2);

        $this->storeManager->setCurrentStore($secondStore);

        $category2 = $this->categoryRepository->get($category2->getId());
        $category2->setUrlKey('category-2');
        $this->categoryRepository->save($category2);

        $this->storeManager->setCurrentStore(StoreModel::DEFAULT_STORE_ID);

        $categoryData3 = $this->categoryFactory->create()->setData(
            [
                'parent_id' => $category2->getId(),
                'name' => 'Category 3',
                'url_key' => 'default-store-category3',
                'is_active' => true
            ]
        );
        $category3 = $this->categoryRepository->save($categoryData3);

        $this->storeManager->setCurrentStore($secondStore);

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setStoreId($secondStore->getId())
            ->addFieldToFilter(
                'entity_id',
                [
                    'in' =>
                        [
                            $category1->getId(),
                            $category2->getId(),
                            $category3->getId()
                        ]
                ]
            );

        $fullPath = [];
        foreach ($categories as $category) {
            $fullPath[] = $category->getUrlKey();
        }

        $fullPath = implode('/', $fullPath) . '.html';
        $this->dispatch($fullPath);
        $response = $this->getResponse();

        $this->assertStringContainsString($fullPath, $response->getBody());
        $this->assertEquals(
            Http::STATUS_CODE_200,
            $response->getHttpResponseCode(),
            'Response code does not match expected value'
        );
    }
}
