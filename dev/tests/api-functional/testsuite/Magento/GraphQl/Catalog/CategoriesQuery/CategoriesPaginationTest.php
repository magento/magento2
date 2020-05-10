<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\CategoriesQuery;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test pagination for the categories query
 */
class CategoriesPaginationTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testDefaultPagination()
    {
        $query = <<<QUERY
{
  categories(filters: {ids: {in: ["3", "4", "5", "6", "7", "8", "9"]}}) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $categories = $response['categories']['items'];
        $this->assertCount(6, $categories);
        $this->assertEquals(count($categories), $response['categories']['total_count']);
        $this->assertArrayHasKey('page_info', $response['categories']);
        $pageInfo = $response['categories']['page_info'];
        $this->assertEquals(1, $pageInfo['current_page']);
        $this->assertEquals(20, $pageInfo['page_size']);
        $this->assertEquals(1, $pageInfo['total_pages']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testPageSize()
    {
        $query = <<<QUERY
{
  categories(
    filters: {ids: {in: ["3", "4", "5", "6", "7", "8", "9"]}}
    pageSize: 2
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $categories = $response['categories']['items'];
        $this->assertCount(2, $categories);
        $this->assertNotEquals(count($categories), $response['categories']['total_count']);
        $this->assertEquals(6, $response['categories']['total_count']);
        $pageInfo = $response['categories']['page_info'];
        $this->assertEquals(1, $pageInfo['current_page']);
        $this->assertEquals(2, $pageInfo['page_size']);
        $this->assertEquals(3, $pageInfo['total_pages']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCurrentPage()
    {
        $query = <<<QUERY
{
  categories(
    filters: {name: {match: "Category"}}
    pageSize: 3
    currentPage: 3
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $categories = $response['categories']['items'];
        $this->assertCount(1, $categories);
        $this->assertEquals(7, $response['categories']['total_count']);
        $pageInfo = $response['categories']['page_info'];
        $this->assertEquals(3, $pageInfo['current_page']);
        $this->assertEquals(3, $pageInfo['page_size']);
        $this->assertEquals(3, $pageInfo['total_pages']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testPaging()
    {
        $baseQuery = <<<QUERY
{
  categories(
    filters: {name: {match: "Category"}}
    pageSize: 2
    currentPage: %s
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;

        $page1Query = sprintf($baseQuery, 1);
        $page1Result = $this->graphQlQuery($page1Query);
        $this->assertEquals('Default Category', $page1Result['categories']['items'][0]['name']);
        $this->assertEquals('Category 1', $page1Result['categories']['items'][1]['name']);
        $this->assertEquals(7, $page1Result['categories']['total_count']);

        $page2Query = sprintf($baseQuery, 2);
        $page2Result = $this->graphQlQuery($page2Query);
        $this->assertEquals('Category 1.1', $page2Result['categories']['items'][0]['name']);
        $this->assertEquals('Category 1.1.1', $page2Result['categories']['items'][1]['name']);

        $lastPageQuery = sprintf($baseQuery, $page1Result['categories']['page_info']['total_pages']);
        $lastPageResult = $this->graphQlQuery($lastPageQuery);
        $this->assertCount(1, $lastPageResult['categories']['items']);
        $this->assertEquals('Category 1.2', $lastPageResult['categories']['items'][0]['name']);
    }

    /**
     */
    public function testCurrentPageZero()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage value must be greater than 0.');

        $query = <<<QUERY
{
  categories(
    filters: {name: {match: "Category"}}
    currentPage: 0
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testPageSizeZero()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('pageSize value must be greater than 0.');

        $query = <<<QUERY
{
  categories(
    filters: {name: {match: "Category"}}
    pageSize: 0
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCurrentPageTooLarge()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage value 6 specified is greater than the 2 page(s) available.');

        $query = <<<QUERY
{
  categories(
    filters: {url_key: {in: ["category-1", "category-1-1", "category-1-1-1"]}}
    pageSize: 2
    currentPage: 6
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }
}
