<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CmsUrlRewrite;

use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test the GraphQL endpoint's URLResolver query to verify canonical URL's are correctly returned.
 */
class UrlResolverTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCMSPageUrlResolver()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $cmsPageId = $page->getId();
        $requestPath = $page->getIdentifier();

        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class);

        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);
        $expectedEntityType = CmsPageUrlRewriteGenerator::ENTITY_TYPE;

        $query = $this->createQuery($requestPath);
        $response = $this->graphQlQuery($query);
        $this->assertEquals($cmsPageId, $response['urlResolver']['id']);
        $this->assertEquals($requestPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper(str_replace('-', '_', $expectedEntityType)), $response['urlResolver']['type']);
        $this->assertEquals(0, $response['urlResolver']['redirectCode']);

        // querying by non seo friendly url path should return seo friendly relative url
        $query = $this->createQuery($targetPath);
        $response = $this->graphQlQuery($query);
        $this->assertEquals($cmsPageId, $response['urlResolver']['id']);
        $this->assertEquals($requestPath, $response['urlResolver']['relative_url']);
        $this->assertEquals(strtoupper(str_replace('-', '_', $expectedEntityType)), $response['urlResolver']['type']);
        $this->assertEquals(0, $response['urlResolver']['redirectCode']);
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testResolveCMSPageWithQueryParameters()
    {
        $page = $this->objectManager->create(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $cmsPageId = $page->getId();
        $requestPath = $page->getIdentifier();
        $requestPath .= '?key=value';

        $query = $this->createQuery($requestPath);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['urlResolver']);
        $this->assertEquals($cmsPageId, $response['urlResolver']['id']);
        $this->assertEquals($requestPath, $response['urlResolver']['relative_url']);
    }

    /**
     * Test resolution of '/' path to home page
     */
    public function testResolveSlash()
    {
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface */
        $scopeConfigInterface = $this->objectManager->get(ScopeConfigInterface::class);
        $homePageIdentifier = $scopeConfigInterface->getValue(
            PageHelper::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE
        );
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(\Magento\Cms\Model\Page::class);
        $page->load($homePageIdentifier);
        $homePageId = $page->getId();
        $query = $this->createQuery('/');
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('urlResolver', $response);
        $this->assertEquals($homePageId, $response['urlResolver']['id']);
        $this->assertEquals($homePageIdentifier, $response['urlResolver']['relative_url']);
        $this->assertEquals('CMS_PAGE', $response['urlResolver']['type']);
        $this->assertEquals(0, $response['urlResolver']['redirectCode']);
    }

    /**
     * @param string $path
     * @return string
     */
    private function createQuery(string $path): string
    {
        return <<<QUERY
{
  urlResolver(url:"{$path}")
  {
   id
   relative_url
   type
   redirectCode
  }
}
QUERY;
    }
}
