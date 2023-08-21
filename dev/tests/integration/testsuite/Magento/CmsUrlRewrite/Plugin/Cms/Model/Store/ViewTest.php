<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test for plugin which is listening store resource model and on save replace cms page url rewrites.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Store
     */
    private $storeFactory;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var CmsPageUrlPathGenerator
     */
    private $cmsPageUrlPathGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->urlFinder = $this->objectManager->create(UrlFinderInterface::class);
        $this->storeFactory = $this->objectManager->create(StoreFactory::class);
        $this->urlPersist = $this->objectManager->create(UrlPersistInterface::class);
        $this->urlRewriteFactory = $this->objectManager->create(UrlRewriteFactory::class);
        $this->pageRepository = $this->objectManager->create(PageRepositoryInterface::class);
        $this->cmsPageUrlPathGenerator = $this->objectManager->create(CmsPageUrlPathGenerator::class);
    }

    /**
     * Test of replacing cms page url rewrites on create and delete store
     *
     * @magentoDataFixture Magento/Cms/_files/two_cms_page_with_same_url_for_different_stores.php
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testUrlRewritesChangesAfterStoreSave(): void
    {
        $storeId = $this->createStore();
        $this->assertUrlRewritesCount($storeId, 'page100', 1);
        $this->editUrlRewrite($storeId, 'page100');
        $this->saveStore($storeId);
        $this->assertUrlRewritesCount($storeId, 'page100-test', 1);
        $this->deleteStore($storeId);
        $this->assertUrlRewritesCount($storeId, 'page100', 0);
    }

    /**
     * Assert url rewrites count by store id and request path
     *
     * @param int $storeId
     * @param string $requestPath
     * @param int $expectedCount
     */
    private function assertUrlRewritesCount(int $storeId, string $requestPath, int $expectedCount): void
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => $storeId
        ];
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount($expectedCount, $urlRewrites);
    }

    /**
     * Create test store
     */
    private function createStore(): int
    {
        $store = $this->storeFactory->create();
        $store->setCode('test_' . random_int(0, 999))
            ->setName('Test Store')
            ->unsId()
            ->save();

        return (int)$store->getId();
    }

    /**
     * Delete test store
     *
     * @param int $storeId
     */
    private function deleteStore(int $storeId): void
    {
        $store = $this->storeFactory->create();
        $store->load($storeId);
        if ($store !== null) {
            $store->delete();
        }
    }

    /**
     * Edit url rewrite
     *
     * @param int $storeId
     * @param string $pageIdentifier
     */
    private function editUrlRewrite(int $storeId, string $pageIdentifier): void
    {
        $filter = $this->objectManager->create(Filter::class);
        $filter->setField('identifier')->setValue($pageIdentifier);
        $filterGroup = $this->objectManager->create(FilterGroup::class);
        $filterGroup->setFilters([$filter]);
        $searchCriteria = $this->objectManager->create(SearchCriteriaInterface::class);
        $searchCriteria->setFilterGroups([$filterGroup]);
        $pageSearchResults = $this->pageRepository->getList($searchCriteria);
        $pages = $pageSearchResults->getItems();
        /** @var PageInterface $page */
        $cmsPage = array_values($pages)[0];

        $urlRewrite = $this->urlRewriteFactory->create()->setStoreId($storeId)
            ->setEntityType(CmsPageUrlRewriteGenerator::ENTITY_TYPE)
            ->setEntityId($cmsPage->getId())
            ->setRequestPath($cmsPage->getIdentifier() . '-test')
            ->setTargetPath($this->cmsPageUrlPathGenerator->getCanonicalUrlPath($cmsPage))
            ->setIsAutogenerated(0)
            ->setRedirectType(0);

        $this->urlPersist->replace([$urlRewrite]);
    }

    /**
     * Edit test store
     *
     * @param int $storeId
     * @return void
     */
    private function saveStore(int $storeId): void
    {
        $store = $this->storeFactory->create();
        $store->load($storeId);
        if ($store !== null) {
            $store->save();
        }
    }
}
