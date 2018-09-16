<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CmsPageTest extends GraphQlAbstract
{
    /**
     * Verify the fields of CMS Page selected by page_id
     *
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testGetCmsPageById()
    {
        $cmsPage = ObjectManager::getInstance()->get(GetPageByIdentifier::class)->execute('page100', 0);
        $pageId = $cmsPage->getPageId();
        $cmsPageData = $cmsPage->getData();
        $query =
            <<<QUERY
{
  cmsPage(id: $pageId) {
    url_key
    title
    content
    content_heading
    page_layout
    meta_title
    meta_description
    meta_keywords
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals($cmsPageData['identifier'], $response['cmsPage']['url_key']);
        $this->assertEquals($cmsPageData['title'], $response['cmsPage']['title']);
        $this->assertEquals($cmsPageData['content'], $response['cmsPage']['content']);
        $this->assertEquals($cmsPageData['content_heading'], $response['cmsPage']['content_heading']);
        $this->assertEquals($cmsPageData['page_layout'], $response['cmsPage']['page_layout']);
        $this->assertEquals($cmsPageData['meta_title'], $response['cmsPage']['meta_title']);
        $this->assertEquals($cmsPageData['meta_description'], $response['cmsPage']['meta_description']);
        $this->assertEquals($cmsPageData['meta_keywords'], $response['cmsPage']['meta_keywords']);
    }

    /**
     * Verify the message when page_id is not specified.
     */
    public function testGetCmsPageWithoutId()
    {
        $query =
            <<<QUERY
{
  cmsPage {
    url_key
    title
    content
    content_heading
    page_layout
    meta_title
    meta_description
    meta_keywords
  }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Page id should be specified');
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when page_id does not exist.
     */
    public function testGetCmsPageByNonExistentId()
    {
        $query =
            <<<QUERY
{
  cmsPage(id: 0) {
    url_key
    title
    content
    content_heading
    page_layout
    meta_title
    meta_description
    meta_keywords
  }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The CMS page with the "0" ID doesn\'t exist.');
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when CMS Page selected by page_id is disabled
     *
     * @magentoApiDataFixture Magento/Cms/_files/noroute.php
     */
    public function testGetDisabledCmsPageById()
    {
        $cmsPageId = ObjectManager::getInstance()->get(GetPageByIdentifier::class)->execute('no-route', 0)->getPageId();
        $query =
            <<<QUERY
{
  cmsPage(id: $cmsPageId) {
    url_key
    title
    content
    content_heading
    page_layout
    meta_title
    meta_description
    meta_keywords
  }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such entity.');
        $this->graphQlQuery($query);
    }
}
