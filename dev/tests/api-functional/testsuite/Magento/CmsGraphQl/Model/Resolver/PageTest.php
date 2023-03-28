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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Integration\Api\CustomerTokenServiceInterface;
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

        $this->graphqlCache = $objectManager->get(GraphQlCache::class);
        $this->pageRepository = $objectManager->get(PageRepository::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);

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
        $response = $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndPage($response, $page);

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
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
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
        $response = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            $authHeader
        );

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndPage($response, $page);

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
        $response = $getGraphQlClient->invoke($this)->postWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndPage($response, $page);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
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
            $response = $this->graphQlQueryWithResponseHeaders($query);

            $resolverCacheKeyForGuestQuery = $this->getResolverCacheKeyFromResponseAndPage($response, $page);

            $cacheEntry = $this->graphqlCache->load($resolverCacheKeyForGuestQuery);
            $cacheEntryDecoded = json_decode($cacheEntry, true);

            $this->assertEqualsCanonicalizing(
                $this->generateExpectedDataFromPage($page),
                $cacheEntryDecoded
            );

            $this->assertTagsByCacheIdentityAndPage($resolverCacheKeyForGuestQuery, $page);

            $resolverCacheKeys[] = $resolverCacheKeyForGuestQuery;

            // query $page as customer
            $query = $this->getQuery($page->getIdentifier());
            $response = $this->graphQlQueryWithResponseHeaders(
                $query,
                [],
                '',
                $authHeader
            );

            $resolverCacheKeyForUserQuery = $this->getResolverCacheKeyFromResponseAndPage($response, $page);

            $cacheEntry = $this->graphqlCache->load($resolverCacheKeyForUserQuery);
            $cacheEntryDecoded = json_decode($cacheEntry, true);

            $this->assertEqualsCanonicalizing(
                $this->generateExpectedDataFromPage($page),
                $cacheEntryDecoded
            );

            $this->assertTagsByCacheIdentityAndPage($resolverCacheKeyForUserQuery, $page);

            $resolverCacheKeys[] = $resolverCacheKeyForUserQuery;
        }

        foreach ($resolverCacheKeys as $cacheIdentityString) {
            $this->assertNotFalse($this->graphqlCache->load($cacheIdentityString));
        }

        // invalidate first page and assert first two cache identities (guest and user) are invalidated,
        // while the rest are not
        $page = $this->getPageByTitle($titles[0]);
        $page->setMetaDescription('whatever');
        $this->pageRepository->save($page);

        list($page1GuestKey, $page1UserKey, $page2GuestKey, $page2UserKey) = $resolverCacheKeys;

        $this->assertFalse($this->graphqlCache->load($page1GuestKey));
        $this->assertFalse($this->graphqlCache->load($page1UserKey));

        $this->assertNotFalse($this->graphqlCache->load($page2GuestKey));
        $this->assertNotFalse($this->graphqlCache->load($page2UserKey));
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

    private function getResolverCacheKeyFromResponseAndPage(array $response, PageInterface $page): string
    {
        $cacheIdValue = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

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
}
