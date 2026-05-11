<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\SearchCriteria;

use Magento\Framework\Api\Filter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter as UiFulltextFilter;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FulltextFilterPluginTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testApplyOnOrderGridUsesLikeConditionsAndNoMatchAgainst(): void
    {
        /** @var UiFulltextFilter $fulltextFilter */
        $fulltextFilter = $this->objectManager->get(UiFulltextFilter::class);

        /** @var OrderGridCollection $collection */
        $collection = $this->objectManager->create(OrderGridCollection::class);

        /** @var Filter $filter */
        $filter = $this->objectManager->create(Filter::class);
        $filter->setValue('Will');

        $fulltextFilter->apply($collection, $filter);

        $sql = $collection->getSelectSql(true);

        $this->assertStringContainsString("`main_table`.`increment_id` LIKE '%Will%'", $sql);
        $this->assertStringContainsString("`main_table`.`billing_name` LIKE '%Will%'", $sql);
        $this->assertStringContainsString("`main_table`.`shipping_name` LIKE '%Will%'", $sql);
        $this->assertStringContainsString("`main_table`.`customer_email` LIKE '%Will%'", $sql);

        $this->assertStringNotContainsString('MATCH(', $sql);
        $this->assertStringNotContainsString('AGAINST(', $sql);
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testApplyOnOrderGridWithEmptyValueDoesNothing(): void
    {
        /** @var UiFulltextFilter $fulltextFilter */
        $fulltextFilter = $this->objectManager->get(UiFulltextFilter::class);

        /** @var OrderGridCollection $collection */
        $collection = $this->objectManager->create(OrderGridCollection::class);
        $initialSql = $collection->getSelectSql(true);

        /** @var Filter $filter */
        $filter = $this->objectManager->create(Filter::class);
        $filter->setValue('   '); // empty after trim

        $fulltextFilter->apply($collection, $filter);

        $sql = $collection->getSelectSql(true);

        // No LIKE or MATCH added
        $this->assertSame($initialSql, $sql);
        $this->assertStringNotContainsString('LIKE', $sql);
        $this->assertStringNotContainsString('MATCH(', $sql);
        $this->assertStringNotContainsString('AGAINST(', $sql);
    }
}
