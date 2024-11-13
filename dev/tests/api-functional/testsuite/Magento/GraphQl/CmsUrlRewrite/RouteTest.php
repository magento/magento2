<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CmsUrlRewrite;

use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test the GraphQL endpoint's route query to verify URL route information is correctly returned.
 */
class RouteTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testCMSPageUrlResolver()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->objectManager->get(Page::class);
        $page->load('page100');
        $requestPath = $page->getIdentifier();

        /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $urlPathGenerator */
        $urlPathGenerator = $this->objectManager->get(CmsPageUrlPathGenerator::class);

        /** @param \Magento\Cms\Api\Data\PageInterface $page */
        $targetPath = $urlPathGenerator->getCanonicalUrlPath($page);
        $expectedEntityType = CmsPageUrlRewriteGenerator::ENTITY_TYPE;

        $query = $this->createQuery($requestPath);
        $response = $this->graphQlQuery($query);
        $this->assertEquals($requestPath, $response['route']['relative_url']);
        $this->assertEquals(strtoupper(str_replace('-', '_', $expectedEntityType)), $response['route']['type']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals($page->getIdentifier(), $response['route']['identifier']);

        // querying by non seo friendly url path should return seo friendly relative url
        $query = $this->createQuery($targetPath);
        $response = $this->graphQlQuery($query);
        $this->assertEquals($requestPath, $response['route']['relative_url']);
        $this->assertEquals(strtoupper(str_replace('-', '_', $expectedEntityType)), $response['route']['type']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals($page->getIdentifier(), $response['route']['identifier']);
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testResolveCMSPageWithQueryParameters()
    {
        $page = $this->objectManager->create(Page::class);
        $page->load('page100');
        $requestPath = $page->getIdentifier();
        $requestPath .= '?key=value';

        $query = $this->createQuery($requestPath);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['route']);
        $this->assertEquals($requestPath, $response['route']['relative_url']);
        $this->assertEquals($page->getIdentifier(), $response['route']['identifier']);
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
        $page = $this->objectManager->get(Page::class);
        $page->load($homePageIdentifier);
        $query = $this->createQuery('/');
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);
        $this->assertEquals($homePageIdentifier, $response['route']['relative_url']);
        $this->assertEquals('CMS_PAGE', $response['route']['type']);
        $this->assertEquals(0, $response['route']['redirect_code']);
        $this->assertEquals($page->getIdentifier(), $response['route']['identifier']);
    }

    /**
     * @param string $path
     * @return string
     */
    private function createQuery(string $path): string
    {
        return <<<QUERY
{
  route(url:"{$path}")
  {
    relative_url
    type
    redirect_code
    __typename
    ...on CmsPage {
      identifier
    }
  }
}
QUERY;
    }
}
