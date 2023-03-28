<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Cms\Model\PageRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GraphQlCache
     */
    private $graphqlCache;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalCacheStateEnabledStatus;

    protected function setUp(): void
    {
        $this->objectManager = $objectManager = ObjectManager::getInstance();

        // extend object manager with graphql area preferences in order to get idFactorProviders later in
        // calculateCacheIdByStoreAndCustomer; objectManager is reinitialized in tearDown
        $configLoader = $objectManager->get(ConfigLoader::class);
        $objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));

        $this->graphqlCache = $objectManager->get(GraphQlCache::class);
        $this->pageRepository = $objectManager->get(PageRepository::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);

        $this->cacheState = $objectManager->get(CacheState::class);
        $this->originalCacheStateEnabledStatus = $this->cacheState->isEnabled(GraphQlCache::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(GraphQlCache::TYPE_IDENTIFIER, true);
    }

    protected function tearDown(): void
    {
        // clean graphql resolver cache and reset to original enablement status
        $this->objectManager->get(GraphQlCache::class)->clean();
        $this->cacheState->setEnabled(
            GraphQlCache::TYPE_IDENTIFIER,
            $this->originalCacheStateEnabledStatus
        );

        Bootstrap::getInstance()->reinitialize();
    }

    /**
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheAndInvalidationAsGuest()
    {
        $page = $this->getPageByTitle('Page with 1column layout');

        $query = $this->getQuery($page->getIdentifier());
        $this->graphQlQuery($query);

        $store = $this->storeManager->getDefaultStoreView();

        $guestCacheId = $this->calculateCacheIdByStoreAndCustomer($store);
        $cacheIdentityString = $this->getResolverCacheKeyByCacheIdAndPage($guestCacheId, $page);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromPage($page),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndPage($cacheIdentityString, $page);

        // update CMS page and assert cache is invalidated
        $page->setContent('something different');
        $this->pageRepository->save($page);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry still exists for CMS page'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheAndInvalidationAsCustomer()
    {
        $authHeader = [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken(
                'customer@example.com',
                'password'
            )
        ];

        $page = $this->getPageByTitle('Page with 1column layout');
        $query = $this->getQuery($page->getIdentifier());
        $this->graphQlQuery(
            $query,
            [],
            '',
            $authHeader
        );

        $store = $this->storeManager->getDefaultStoreView();
        $customer = $this->customerRepository->get('customer@example.com');

        $customerCacheId = $this->calculateCacheIdByStoreAndCustomer($store, $customer);
        $cacheIdentityString = $this->getResolverCacheKeyByCacheIdAndPage($customerCacheId, $page);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromPage($page),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndPage($cacheIdentityString, $page);

        // update CMS page and assert cache is invalidated
        $page->setIdentifier('1-column-page-different-identifier');
        $this->pageRepository->save($page);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry still exists for CMS page'
        );
    }

    /**
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     * @throws \ReflectionException
     */
    public function testCmsPageResolverCacheWithPostRequest()
    {
        $page = $this->getPageByTitle('Page with 1column layout');

        $getGraphQlClient = new \ReflectionMethod($this, 'getGraphQlClient');
        $getGraphQlClient->setAccessible(true);

        $query = $this->getQuery($page->getIdentifier());
        $getGraphQlClient->invoke($this)->post($query);

        $store = $this->storeManager->getDefaultStoreView();

        $guestCacheId = $this->calculateCacheIdByStoreAndCustomer($store);
        $cacheIdentityString = $this->getResolverCacheKeyByCacheIdAndPage($guestCacheId, $page);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromPage($page),
            $cacheEntryDecoded
        );
    }

    private function generateExpectedDataFromPage(PageInterface $page): array
    {
        return [
            'page_id' => $page->getId(),
            'identifier' => $page->getIdentifier(),
            'url_key' => $page->getIdentifier(),
            'title' => $page->getTitle(),
            'content' => $page->getContent(),
            'content_heading' => $page->getContentHeading(),
            'page_layout' => $page->getPageLayout(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_title' => $page->getMetaTitle(),
            'meta_description' => $page->getMetaDescription(),
        ];
    }

    private function assertTagsByCacheIdentityAndPage(string $cacheIdentityString, PageInterface $page): void
    {
        $lowLevelFrontendCache = $this->graphqlCache->getLowLevelFrontend();
        $cacheIdPrefix = $lowLevelFrontendCache->getOption('cache_id_prefix');
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheIdentityString);
        $tags = $metadatas['tags'];

        $this->assertEqualsCanonicalizing(
            [
                $cacheIdPrefix . strtoupper(CmsPage::CACHE_TAG),
                $cacheIdPrefix . strtoupper(CmsPage::CACHE_TAG) . '_' . $page->getId(),
                $cacheIdPrefix . strtoupper(GraphQlCache::CACHE_TAG),
                $cacheIdPrefix . 'MAGE',
            ],
            $tags
        );
    }

    private function getPageByTitle(string $title): PageInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('title', $title)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        /** @var PageInterface $page */
        $page = reset($pages);

        return $page;
    }

    private function getQuery(string $identifier): string
    {
        return <<<QUERY
{
  cmsPage(identifier: "$identifier") {
    title
  }
}
QUERY;
    }

    private function getResolverCacheKeyByCacheIdAndPage(string $cacheIdValue, PageInterface $page): string
    {
        $cacheIdQueryPayloadMetadata = sprintf('CmsPage%s', json_encode([
            'identifier' => $page->getIdentifier(),
        ]));

        $cacheIdParts = [
            GraphQlCache::CACHE_TAG,
            $cacheIdValue,
            sha1($cacheIdQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheIdParts));
    }

    /**
     * @param StoreInterface $store
     * @param CustomerInterface|null $customer - guest if null
     * @return string
     */
    private function calculateCacheIdByStoreAndCustomer(
        StoreInterface $store,
        ?CustomerInterface $customer = null
    ): string {
        $contextFactory = $this->getMockForAbstractClass(ContextFactoryInterface::class);

        $cacheIdCalculator = $this->objectManager->create(CacheIdCalculator::class, [
            'contextFactory' => $contextFactory,
        ]);

        $context = $this->getMockForAbstractClass(ContextInterface::class);

        $contextFactory
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($context);

        $context
            ->expects($customer ? $this->atLeastOnce() : $this->any())
            ->method('getUserId')
            ->willReturn($customer ? (int) $customer->getId() : null);

        $extensionAttributes = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $context
            ->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $extensionAttributes
            ->expects($this->atLeastOnce())
            ->method('getIsCustomer')
            ->willReturn((bool) $customer);

        $extensionAttributes
            ->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($store);

        $extensionAttributes
            ->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn($customer ? $customer->getGroupId() : CustomerGroupInterface::NOT_LOGGED_IN_ID);

        return $cacheIdCalculator->getCacheId();
    }
}
