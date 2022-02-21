<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\ResourceModel\Page\Grid;

use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verifies that filter condition date is being converted to config timezone before select sql query
     *
     * @dataProvider getCollectionFiltersDataProvider
     * @return void
     */
    public function testAddFieldToFilter($field): void
    {
        $filterDate = "2021-12-06 00:00:00";
        /** @var TimezoneInterface $timeZone */
        $timeZone = $this->objectManager->get(TimezoneInterface::class);
        /** @var Collection $gridCollection */
        $gridCollection = $this->objectManager->create(
            Collection::class,
            [
                'mainTable' => 'cms_page',
                'resourceModel' => Page::class
            ]
        );
        $convertedDate = $timeZone->convertConfigTimeToUtc($filterDate);

        $collection = $gridCollection->addFieldToFilter($field, ['qteq' => $filterDate]);
        $expectedSelectCondition = "`{$field}` = '{$convertedDate}'";

        $this->assertStringContainsString($expectedSelectCondition, $collection->getSelectSql(true));
    }

    /**
     * @return array
     */
    public function getCollectionFiltersDataProvider(): array
    {
        return [
            'cms_page_collection_for_creation_time' => [
                'field' => 'creation_time',
            ],
            'cms_page_collection_for_order_update_time' => [
                'field' => 'update_time',
            ],
        ];
    }
}
