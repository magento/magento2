<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Cms;

use Magento\UrlRewrite\Test\Fixture\CmsPage;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test to return category aggregations
 */
class CmsPageErrorHandlingTest extends GraphQlAbstract
{
    #[
        DataFixture(
            CmsPage::class,
            [
                'is_active' => 1,
                "identifier"=>"enabled-test-page"
            ],
            as: 'enabled_page'
        )
    ]
    public function testGetEnabledCmsPageReturnsData()
    {
        $query = $this->buildCmsPageQuery('enabled-test-page');
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);
        $this->assertEquals('enabled-test-page', $response['route']['relative_url']);
        $this->assertEquals('CMS_PAGE', $response['route']['type']);
    }

    #[
        DataFixture(
            CmsPage::class,
            [
                'is_active' => 0,
                "identifier"=>"disabled-test-page"
            ],
            as: 'enabled_page'
        )
    ]
    public function testGetDisabledCmsPageReturnsData()
    {
        $query = $this->buildCmsPageQuery('disabled-test-page');
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('route', $response);

        $this->assertNull(
            $response['route'],
            'Disabled CMS page should return null instead of throwing error'
        );
    }

    /**
     * Build GraphQL query for CMS page
     *
     * @param string $value
     * @return string
     */
    private function buildCmsPageQuery(string $value): string
    {
        return <<<QUERY
{
 route(url: "{$value}" ) {
   redirect_code
   relative_url
   type
 }
}
QUERY;
    }
}
