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
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class PageTest extends GraphQlAbstract
{
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

    protected function setUp(): void
    {
        $this->graphqlCache = ObjectManager::getInstance()->get(GraphQlCache::class);
        $this->pageRepository = ObjectManager::getInstance()->get(PageRepository::class);
        $this->searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @return void
     */
    public function testCmsPageResolverIsCached()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('title', 'Page with 1column layout')
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        /** @var PageInterface $page */
        $page = reset($pages);

        $query = $this->getQuery($page->getIdentifier());
        $response = $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getCacheIdentityStringFromResponseAndPage($response, $page);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            [
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
            ],
            $cacheEntryDecoded
        );

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

        // update CMS page and assert cache is invalidated
        $page->setContent('something different');
        $this->pageRepository->save($page);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry still exists for CMS page'
        );
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
     * @param array $response
     * @param PageInterface $page
     * @return string
     */
    private function getCacheIdentityStringFromResponseAndPage(array $response, PageInterface $page): string
    {
        $cacheIdHeaderValue = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        $cacheIdQueryPayloadMetadata = sprintf('CmsPage%s', json_encode([
            'identifier' => $page->getIdentifier(),
        ]));

        $cacheIdParts = [
            GraphQlCache::CACHE_TAG,
            $cacheIdHeaderValue,
            sha1($cacheIdQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheIdParts));
    }
}
