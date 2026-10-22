<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(false)]
class ParentSpecialPriceTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(ConfigurableAttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, ['price' => 10], 'child1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$child1$', '$child2$']],
            'configurable'
        ),
    ]
    public function testParentSpecialPriceDoesNotAffectIndexedMinAndMaxPrice(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $parentId = (int)$this->fixtures->get('configurable')->getId();
        $childIds = [
            (int)$this->fixtures->get('child1')->getId(),
            (int)$this->fixtures->get('child2')->getId(),
        ];

        $objectManager->get(ProductAction::class)->updateAttributes(
            [$parentId],
            ['price' => 30, 'special_price' => 5],
            Store::DEFAULT_STORE_ID
        );
        $objectManager->get(PriceIndexerProcessor::class)->reindexList([...$childIds, $parentId], true);

        $rows = $objectManager->get(GetPriceIndexDataByProductId::class)->execute(
            $parentId,
            Group::NOT_LOGGED_IN_ID,
            (int)$objectManager->get(WebsiteRepositoryInterface::class)->get('base')->getId()
        );

        $this->assertCount(1, $rows);
        $this->assertEquals(10, $rows[0]['min_price']);
        $this->assertEquals(20, $rows[0]['max_price']);
    }
}
