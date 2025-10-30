<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\MediaGalleryApi\Api\SearchAssetsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify SearchAssets By searchCriteria
 */
class SearchAssetsTest extends TestCase
{
    private const FIXTURE_ASSET_PATH = 'testDirectory/path.jpg';

    /**
     * @var SearchAssetsInterfcae
     */
    private $searchAssets;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filterBuilder = Bootstrap::getObjectManager()->get(FilterBuilder::class);
        $this->filterGroupBuilder = Bootstrap::getObjectManager()->get(FilterGroupBuilder::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->searchAssets = Bootstrap::getObjectManager()->get(SearchAssetsInterface::class);
    }

    /**
     * Verify search asstes by searching with search criteria
     *
     * @dataProvider searchCriteriaProvider
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(array $searchCriteriaData): void
    {
        $titleFilter = $this->filterBuilder->setField($searchCriteriaData['field'])
                ->setConditionType($searchCriteriaData['conditionType'])
                ->setValue($searchCriteriaData['value'])
                ->create();
        $searchCriteria = $this->searchCriteriaBuilder
                ->setFilterGroups([$this->filterGroupBuilder->setFilters([$titleFilter])->create()])
                ->create();

        $assets = $this->searchAssets->execute($searchCriteria);

        $this->assertCount(1, $assets);
        $this->assertEquals($assets[0]->getPath(), self::FIXTURE_ASSET_PATH);
    }

    /**
     * Search criteria params provider
     *
     * @return array
     */
    public static function searchCriteriaProvider(): array
    {
        return [
            [
                ['field' =>  'id', 'conditionType' => 'eq', 'value' => 2020],
            ],
            [
                ['field' =>  'title', 'conditionType' => 'fulltext', 'value' => 'Img'],
            ],
            [
                ['field' =>  'content_type', 'conditionType' => 'eq', 'value' => 'image']
            ],
            [
                ['field' =>  'description', 'conditionType' => 'fulltext', 'value' => 'description']
            ]
        ];
    }
}
