<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Testing seach in grid.
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
 */
class FulltextGridSearchTest extends AbstractBackendController
{
    /**
     * Checks a fulltext grid search by CMS page title.
     *
     * @param string $query
     * @param int $expectedRows
     * @param array $expectedTitles
     * @dataProvider queryDataProvider
     */
    public function testSearchByTitle(string $query, int $expectedRows, array $expectedTitles)
    {
        $url = 'backend/mui/index/render/?namespace=cms_page_listing&search=' . $query;

        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine('Accept', 'application/json');
        $this->dispatch($url);
        $response = $this->getResponse();
        $data = json_decode($response->getBody(), true);
        self::assertEquals($expectedRows, $data['totalRecords']);

        $titleList = array_column($data['items'], 'title');
        self::assertEquals($expectedTitles, $titleList);
    }

    /**
     * Gets list of variations with different search queries.
     *
     * @return array
     */
    public static function queryDataProvider(): array
    {
        return [
            [
                'query' => 'simple',
                'expectedRows' => 3,
                'expectedTitles' => ['simplePage', 'simplePage01', '01simplePage']
            ],
            [
                'query' => 'page01',
                'expectedRows' => 1,
                'expectedTitles' => ['simplePage01']
            ],
            [
                'query' => '01simple',
                'expectedRows' => 1,
                'expectedTitles' => ['01simplePage']
            ],
        ];
    }
}
