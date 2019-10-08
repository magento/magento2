<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ResultTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testIndexActionTranslation()
    {
        $this->markTestSkipped('MAGETWO-44910');
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Locale\ResolverInterface::class)->setLocale('de_DE');

        $this->getRequest()->setParam('q', 'query_text');
        $this->dispatch('catalogsearch/result');

        $responseBody = $this->getResponse()->getBody();
        $this->assertNotContains('for="search">Search', $responseBody);
        $this->assertStringMatchesFormat('%aSuche%S%a', $responseBody);

        $this->assertNotContains('Search entire store here...', $responseBody);
        $this->assertContains('Den gesamten Shop durchsuchen...', $responseBody);
    }

    public function testIndexActionXSSQueryVerification()
    {
        $escaper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Escaper::class);
        $this->getRequest()->setParam('q', '<script>alert(1)</script>');
        $this->dispatch('catalogsearch/result');

        $responseBody = $this->getResponse()->getBody();
        $data = '<script>alert(1)</script>';
        $this->assertNotContains($data, $responseBody);
        $this->assertContains($escaper->escapeHtml($data), $responseBody);
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query_redirect.php
     */
    public function testRedirect()
    {
        $this->dispatch('/catalogsearch/result/?q=query_text');
        $responseBody = $this->getResponse();

        $this->assertTrue($responseBody->isRedirect());
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query_redirect.php
     */
    public function testNoRedirectIfCurrentUrlAndRedirectTermAreSame()
    {
        $this->dispatch('/catalogsearch/result/?q=query_text&cat=41');
        $responseBody = $this->getResponse();

        $this->assertFalse($responseBody->isRedirect());
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testPopularity()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $query \Magento\Search\Model\Query */
        $query = $objectManager->create(\Magento\Search\Model\Query::class);
        $query->loadByQueryText('query_text');
        $this->assertEquals(1, $query->getPopularity());

        $this->dispatch('catalogsearch/searchTermsLog/save?q=query_text');

        $responseBody = $this->getResponse()->getBody();
        $data = '"success":true';
        $this->assertContains($data, $responseBody);

        $query->loadByQueryText('query_text');
        $this->assertEquals(2, $query->getPopularity());
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/popular_query.php
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testPopularSearch()
    {
        $this->cacheAndPopularitySetup();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $query \Magento\Search\Model\Query */
        $query = $objectManager->create(\Magento\Search\Model\Query::class);
        $query->loadByQueryText('popular_query_text');
        $this->assertEquals(100, $query->getPopularity());

        $this->dispatch('/catalogsearch/result/?q=popular_query_text');

        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('Search results for: &#039;popular_query_text&#039;', $responseBody);
        $this->assertContains('/catalogsearch/searchTermsLog/save/', $responseBody);

        $query->loadByQueryText('popular_query_text');
        $this->assertEquals(100, $query->getPopularity());
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/popular_query.php
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testPopularSearchWithAdditionalRequestParameters()
    {
        $this->cacheAndPopularitySetup();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $query \Magento\Search\Model\Query */
        $query = $objectManager->create(\Magento\Search\Model\Query::class);
        $query->loadByQueryText('popular_query_text');
        $this->assertEquals(100, $query->getPopularity());

        $this->dispatch('/catalogsearch/result/?q=popular_query_text&additional_parameters=some');

        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('Search results for: &#039;popular_query_text&#039;', $responseBody);
        $this->assertNotContains('/catalogsearch/searchTermsLog/save/', $responseBody);

        $query->loadByQueryText('popular_query_text');
        $this->assertEquals(101, $query->getPopularity());
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/popular_query.php
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testNotPopularSearch()
    {
        $this->cacheAndPopularitySetup();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $query \Magento\Search\Model\Query */
        $query = $objectManager->create(\Magento\Search\Model\Query::class);
        $query->loadByQueryText('query_text');
        $this->assertEquals(1, $query->getPopularity());

        $this->dispatch('/catalogsearch/result/?q=query_text');

        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('Search results for: &#039;query_text&#039;', $responseBody);
        $this->assertNotContains('/catalogsearch/searchTermsLog/save/', $responseBody);

        $query->loadByQueryText('query_text');
        $this->assertEquals(2, $query->getPopularity());
    }

    private function cacheAndPopularitySetup()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $scopeConfig \Magento\Framework\App\MutableScopeConfig */
        $scopeConfig = $objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            \Magento\Search\Model\PopularSearchTerms::XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS,
            1,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = $objectManager->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, true);

        /** @var $fpc \Magento\PageCache\Model\Cache\Type */
        $fpc = $objectManager->get(\Magento\PageCache\Model\Cache\Type::class);
        $fpc->clean();
    }
}
