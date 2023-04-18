<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CmsGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Cms\Model\PageRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Frontend\Factory as CacheFrontendFactory;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator\ProviderInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\App\State;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends GraphQlAbstract
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

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
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool
     */
    private $originalCacheStateEnabledStatus;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $initialAppArea;

    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();

        $this->graphQlResolverCache = $this->objectManager->get(GraphQlResolverCache::class);
        $this->pageRepository = $this->objectManager->get(PageRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);

        $this->cacheState = $this->objectManager->get(CacheState::class);
        $this->originalCacheStateEnabledStatus = $this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, true);
        // test has to be executed in graphql area
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        /** @var State $appArea */
        $appArea = $this->objectManager->get(State::class);
        $this->initialAppArea = $appArea->getAreaCode();
        $this->objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));
        $this->resetUserInfoContext();
    }

    protected function tearDown(): void
    {
        // clean graphql resolver cache and reset to original enablement status
        $this->graphQlResolverCache->clean();
        $this->cacheState->setEnabled(
            GraphQlResolverCache::TYPE_IDENTIFIER,
            $this->originalCacheStateEnabledStatus
        );
        /** @var ConfigLoader $configLoader */
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $this->objectManager->configure($configLoader->load($this->initialAppArea));
        $this->objectManager->removeSharedInstance(\Magento\GraphQl\Model\Query\ContextFactory::class);
        $this->objectManager->removeSharedInstance(\Magento\GraphQl\Model\Query\Context::class);
        $this->objectManager->removeSharedInstance(\Magento\GraphQl\Model\Query\ContextInterface::class);
        parent::tearDown();
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheAndInvalidationAsGuest()
    {
        $page = $this->getPageByTitle('Page with 1column layout');

        $query = $this->getQuery($page->getIdentifier());
        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyForPage($page);

        $cacheEntry = $this->graphQlResolverCache->load($cacheIdentityString);
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
            $this->graphQlResolverCache->test($cacheIdentityString),
            'Cache entry still exists for CMS page'
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheAndInvalidationAsCustomer()
    {
        $this->initUserInfoContext('customer@example.com');

        $authHeader = [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken(
                'customer@example.com',
                'password'
            )
        ];

        $page = $this->getPageByTitle('Page with 1column layout');
        $query = $this->getQuery($page->getIdentifier());
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            $authHeader
        );

        $cacheIdentityString = $this->getResolverCacheKeyForPage($page);

        $cacheEntry = $this->graphQlResolverCache->load($cacheIdentityString);
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
            $this->graphQlResolverCache->test($cacheIdentityString),
            'Cache entry still exists for CMS page'
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
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
        $getGraphQlClient->invoke($this)->postWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyForPage($page);

        $cacheEntry = $this->graphQlResolverCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromPage($page),
            $cacheEntryDecoded
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheGeneratesSeparateEntriesBasedOnArgumentsAndContext()
    {
        $titles = ['Page with 1column layout', 'Page with unavailable layout'];

        $authHeader = [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken(
                'customer@example.com',
                'password'
            )
        ];

        foreach ($titles as $title) {
            $page = $this->getPageByTitle($title);

            // query $page as guest
            $query = $this->getQuery($page->getIdentifier());
            $this->graphQlQueryWithResponseHeaders($query);

            $this->resetUserInfoContext();
            $resolverCacheKeyForGuestQuery = $this->getResolverCacheKeyForPage($page);

            $cacheEntry = $this->graphQlResolverCache->load($resolverCacheKeyForGuestQuery);
            $cacheEntryDecoded = json_decode($cacheEntry, true);

            $this->assertEqualsCanonicalizing(
                $this->generateExpectedDataFromPage($page),
                $cacheEntryDecoded
            );

            $this->assertTagsByCacheIdentityAndPage($resolverCacheKeyForGuestQuery, $page);

            $resolverCacheKeys[] = $resolverCacheKeyForGuestQuery;

            // query $page as customer
            $query = $this->getQuery($page->getIdentifier());
            $this->graphQlQueryWithResponseHeaders(
                $query,
                [],
                '',
                $authHeader
            );

            $this->initUserInfoContext('customer@example.com');
            $resolverCacheKeyForUserQuery = $this->getResolverCacheKeyForPage($page);

            $cacheEntry = $this->graphQlResolverCache->load($resolverCacheKeyForUserQuery);
            $cacheEntryDecoded = json_decode($cacheEntry, true);

            $this->assertEqualsCanonicalizing(
                $this->generateExpectedDataFromPage($page),
                $cacheEntryDecoded
            );

            $this->assertTagsByCacheIdentityAndPage($resolverCacheKeyForUserQuery, $page);

            $resolverCacheKeys[] = $resolverCacheKeyForUserQuery;
        }

        // assert that every cache key is unique
        $this->assertCount(count($resolverCacheKeys), array_unique($resolverCacheKeys));

        foreach ($resolverCacheKeys as $cacheIdentityString) {
            $this->assertNotFalse($this->graphQlResolverCache->load($cacheIdentityString));
        }

        // invalidate first page and assert first two cache identities (guest and user) are invalidated,
        // while the rest are not
        $page = $this->getPageByTitle($titles[0]);
        $page->setMetaDescription('whatever');
        $this->pageRepository->save($page);

        list($page1GuestKey, $page1UserKey, $page2GuestKey, $page2UserKey) = $resolverCacheKeys;

        $this->assertFalse($this->graphQlResolverCache->load($page1GuestKey));
        $this->assertFalse($this->graphQlResolverCache->load($page1UserKey));

        $this->assertNotFalse($this->graphQlResolverCache->load($page2GuestKey));
        $this->assertNotFalse($this->graphQlResolverCache->load($page2UserKey));
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testCmsPageResolverCacheInvalidatesWhenPageGetsDeleted()
    {
        // cache page1
        $page1 = $this->getPageByTitle('Page with 1column layout');

        $query = $this->getQuery($page1->getIdentifier());
        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityStringPage1 = $this->getResolverCacheKeyForPage($page1);

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage1)
        );

        // cache page2
        $page2 = $this->getPageByTitle('Page with unavailable layout');

        $query = $this->getQuery($page2->getIdentifier());
        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityStringPage2 = $this->getResolverCacheKeyForPage($page2);

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage2)
        );

        // delete page1 and assert cache is invalidated
        $this->pageRepository->delete($page1);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheIdentityStringPage1),
            'Cache entry still exists for deleted CMS page'
        );

        // assert page2 cache entry still exists
        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage2)
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheInvalidatesWhenPageGetsDisabled()
    {
        // cache page1
        $page1 = $this->getPageByTitle('Page with 1column layout');

        $query = $this->getQuery($page1->getIdentifier());
        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityStringPage1 = $this->getResolverCacheKeyForPage($page1);

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage1)
        );

        // cache page2
        $page2 = $this->getPageByTitle('Page with unavailable layout');

        $query = $this->getQuery($page2->getIdentifier());
        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityStringPage2 = $this->getResolverCacheKeyForPage($page2);

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage2)
        );

        // disable page 1
        $page1->setIsActive(false);
        $this->pageRepository->save($page1);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheIdentityStringPage1),
            'Cache entry still exists for disabled CMS page'
        );

        // assert page2 cache entry still exists
        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityStringPage2)
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverCacheDoesNotSaveNonExistentCmsPage()
    {
        $nonExistentPage = ObjectManager::getInstance()->create(PageInterface::class);
        $nonExistentPage->setIdentifier('non-existent-page');

        $query = $this->getQuery($nonExistentPage->getIdentifier());

        try {
            $this->graphQlQueryWithResponseHeaders($query);
            $this->fail('Expected exception was not thrown');
        } catch (ResponseContainsErrorsException $e) {
            // expected exception
        }

        $cacheIdentityString = $this->getResolverCacheKeyForPage($nonExistentPage);

        $this->assertFalse(
            $this->graphQlResolverCache->load($cacheIdentityString)
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsResolverCacheIsInvalidatedAfterChangingItsStoreView()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->getPageByTitle('Page with 1column layout');

        // query first page in default store and assert cache entry is created; use default store header
        $query = $this->getQuery($page->getIdentifier());

        $this->graphQlQueryWithResponseHeaders(
            $query
        );

        $cacheIdentityString = $this->getResolverCacheKeyForPage($page);

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheIdentityString)
        );

        // change store id of page
        $secondStoreViewId = $this->storeManager->getStore('fixture_second_store')->getId();
        $page->setStoreId($secondStoreViewId);
        $this->pageRepository->save($page);

        // assert cache entry is invalidated
        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheIdentityString)
        );
    }

    /**
     * Test that resolver cache is saved with default TTL
     *
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCacheExpirationTimeUsesDefaultDirective()
    {
        $page = $this->getPageByTitle('Page with 1column layout');
        $query = $this->getQuery($page->getIdentifier());
        $this->graphQlQueryWithResponseHeaders(
            $query
        );

        $cacheIdentityString = $this->getResolverCacheKeyForPage($page);

        $lowLevelFrontendCache = $this->graphQlResolverCache->getLowLevelFrontend();
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheIdentityString);

        $this->assertEquals(
            $metadatas['mtime'] + CacheFrontendFactory::DEFAULT_LIFETIME,
            $metadatas['expire']
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
        $lowLevelFrontendCache = $this->graphQlResolverCache->getLowLevelFrontend();
        $cacheIdPrefix = $lowLevelFrontendCache->getOption('cache_id_prefix');
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheIdentityString);
        $tags = $metadatas['tags'];

        $this->assertEqualsCanonicalizing(
            [
                $cacheIdPrefix . strtoupper(CmsPage::CACHE_TAG) . '_' . $page->getId(),
                $cacheIdPrefix . strtoupper(GraphQlResolverCache::CACHE_TAG),
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

    /**
     * Initialize test-scoped user context with user by his email.
     *
     * @param string $customerEmail
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function initUserInfoContext(string $customerEmail)
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customerModel = $customerRepository->get($customerEmail);
        $userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->onlyMethods(['getUserId', 'getUserType'])->disableOriginalConstructor()->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->willReturn($customerModel->getId());
        $userContextMock->expects($this->any())->method('getUserType')->willReturn(3);
        /** @var \Magento\GraphQl\Model\Query\ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(\Magento\GraphQl\Model\Query\ContextFactory::class);
        $contextFactory->create($userContextMock);
    }

    /**
     * Reset test-scoped user context to guest.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function resetUserInfoContext()
    {
        $userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->onlyMethods(['getUserId', 'getUserType'])->disableOriginalConstructor()->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->willReturn(0);
        $userContextMock->expects($this->any())->method('getUserType')->willReturn(4);
        // test has to be executed in graphql area
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $this->objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));
        /** @var \Magento\GraphQl\Model\Query\ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(\Magento\GraphQl\Model\Query\ContextFactory::class);
        $contextFactory->create($userContextMock);
    }

    /**
     * Create resolver key with key calculator retriever vis the actual key provider.
     *
     * @param PageInterface $page
     * @return string
     */
    public function getResolverCacheKeyForPage(PageInterface $page): string
    {
        $resolverMock = $this->getMockBuilder(\Magento\CmsGraphQl\Model\Resolver\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ProviderInterface $cacheKeyCalculatorProvider */
        $cacheKeyCalculatorProvider = $this->objectManager->get(ProviderInterface::class);
        $cacheKey = $cacheKeyCalculatorProvider->getKeyCalculatorForResolver($resolverMock)->calculateCacheKey();

        $cacheIdQueryPayloadMetadata = sprintf('CmsPage%s', json_encode([
            'identifier' => $page->getIdentifier(),
        ]));

        $cacheIdParts = [
            GraphQlResolverCache::CACHE_TAG,
            $cacheKey,
            sha1($cacheIdQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheIdParts));
    }
}
