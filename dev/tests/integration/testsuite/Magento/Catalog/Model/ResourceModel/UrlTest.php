<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Catalog Url Resource Model.
 */
class UrlTest extends TestCase
{
    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Url
     */
    private Url $urlResource;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $objectManager->create(CategoryRepositoryInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->storeManager = $objectManager->create(StoreManagerInterface::class);
        $this->urlResource = $objectManager->create(Url::class);
    }

    /**
     * Test that scope is respected for the is_active flag.
     *
     * @return void
     * @throws NoSuchEntityException|CouldNotSaveException
     */
    #[
        DbIsolation(true),
        DataFixture(CategoryFixture::class, [
            'name' => 'Enabled on default scope',
            'is_active' => '1',
        ], 'c1'),
        DataFixture(CategoryFixture::class, [
            'name' => 'Disabled on default scope',
            'is_active' => '0',
        ], 'c2'),
        DataFixture(CategoryFixture::class, [
            'name' => 'Enabled on default scope, disabled for store',
            'is_active' => '1',
        ], 'c3'),
        DataFixture(CategoryFixture::class, [
            'name' => 'Disabled on default scope, enabled for store',
            'is_active' => '0',
        ], 'c4'),
    ]
    public function testIsActiveScope(): void
    {
        // Get Store ID
        $storeId = (int) $this->storeManager->getStore('default')->getId();

        // Get Category IDs
        $categoryIds = [];
        foreach (['c1', 'c2', 'c3', 'c4'] as $fixtureName) {
            $categoryIds[$fixtureName] = (int) $this->fixtures->get($fixtureName)->getId();
        }

        // Disable c3 for store
        $c3 = $this->categoryRepository->get($categoryIds['c3'], $storeId);
        $c3->setIsActive(false);
        $this->categoryRepository->save($c3);

        // Enable c4 for store
        $c4 = $this->categoryRepository->get($categoryIds['c4'], $storeId);
        $c4->setIsActive(true);
        $this->categoryRepository->save($c4);

        // Check categories
        $categories = $this->urlResource->getCategories($categoryIds, $storeId);
        $this->assertSame('1', $categories[$categoryIds['c1']]->getIsActive());
        $this->assertSame('0', $categories[$categoryIds['c2']]->getIsActive());
        $this->assertSame('0', $categories[$categoryIds['c3']]->getIsActive());
        $this->assertSame('1', $categories[$categoryIds['c4']]->getIsActive());
    }
}
