<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;
use Magento\Framework\App\Area;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryLinkRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    private $categoryLinkRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Emulation
     */
    private $appEmulation;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryLinkRepository = $this->objectManager->get(CategoryLinkRepositoryInterface::class);
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->appEmulation = $this->objectManager->get(Emulation::class);
        parent::setUp();
    }

    /**
     * Make sure that if some custom code uses emulation and assigns products to categories,
     * the categories are not overwritten by loading data via category repository from wrong store.
     * The default store is used in
     * @see \Magento\Catalog\Model\CategoryLinkRepository::save
     *
     * @magentoAppArea crontab
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/saving_product_category_link_under_different_stores.php
     */
    public function testCategoryDataNotChanged()
    {
        $secondStore = $this->storeRepository->get('fixture_second_store');
        $secondStoreId = (int)$secondStore->getId();
        /** @var CategoryProductLinkInterface $categoryProductLink */
        $categoryProductLink = $this->objectManager->create(CategoryProductLinkInterface::class);

        $this->appEmulation->startEnvironmentEmulation($secondStoreId, Area::AREA_FRONTEND, true);

        $categoryProductLink
            ->setCategoryId(113)
            ->setSku('simple116')
            ->setPosition(2);

        $this->categoryLinkRepository->save($categoryProductLink);

        $this->appEmulation->stopEnvironmentEmulation();

        $categoryFromDefaultStore = $this->categoryRepository->get(113);
        $categoryFromSecondStore = $this->categoryRepository->get(113, $secondStoreId);

        $categoryNameFromDefaultStore = $categoryFromDefaultStore->getName();
        $categoryDescriptionFromDefaultStore = $categoryFromDefaultStore->getDescription();

        $categoryNameFromSecondStore = $categoryFromSecondStore->getName();
        $categoryDescriptionFromSecondStore = $categoryFromSecondStore->getDescription();

        $this->assertEquals('Test Category In Default Store', $categoryNameFromDefaultStore);
        $this->assertEquals('Test description in default store', $categoryDescriptionFromDefaultStore);
        $this->assertEquals('Test Category In Second Store', $categoryNameFromSecondStore);
        $this->assertEquals('Test description in second store', $categoryDescriptionFromSecondStore);
    }
}
