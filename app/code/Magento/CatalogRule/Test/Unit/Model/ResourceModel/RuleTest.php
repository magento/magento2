<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Product\ConditionFactory;
use Magento\CatalogRule\Helper\Data;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\Rule\AssociatedEntityMap;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Listener\ReplaceObjectManager\TestProvidesServiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleTest extends TestCase implements TestProvidesServiceInterface
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var Rule
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = $this->createMock(ResourceConnection::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->once())->method('getResources')->willReturn($this->resource);

        $associatedEntitiesMap = $this->createPartialMock(DataObject::class, ['getData']);
        $associatedEntitiesMap->expects($this->any())
            ->method('getData')
            ->willReturn(
                [
                    'website' => [
                        'associations_table' => 'catalogrule_website',
                        'rule_id_field' => 'rule_id',
                        'entity_id_field' => 'website_id'
                    ],
                    'customer_group' => [
                        'associations_table' => 'catalogrule_customer_group',
                        'rule_id_field' => 'rule_id',
                        'entity_id_field' => 'customer_group_id'
                    ],
                ]
            );

        $this->model = new Rule(
            $context,
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(ConditionFactory::class),
            $this->createMock(DateTime\DateTime::class),
            $this->createMock(Config::class),
            $this->createMock(ManagerInterface::class),
            $this->createMock(Data::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(DateTime::class),
            $this->createMock(PriceCurrencyInterface::class),
            null,
            $this->createMock(EntityManager::class),
            $associatedEntitiesMap
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $ruleIds = [1, 2, 5];
        $productIds = [3, 7, 9];
        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(Select::class);
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->resource->expects($this->once())->method('getTableName')->willReturnArgument(0);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $connection->expects($this->once())->method('fetchCol')->willReturn($productIds);
        $select->expects($this->once())
            ->method('from')
            ->with('catalogrule_product', ['product_id'])
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('rule_id IN (?)', $ruleIds)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();

        $this->assertEquals($productIds, $this->model->getProductIdsByRuleIds($ruleIds));
    }

    /**
     * @inheritDoc
     */
    public function getServiceForObjectManager(string $type): ?object
    {
        // phpstan:ignore this is a virtual class
        return $type === AssociatedEntityMap::class
            ? new DataObject()
            : null;
    }
}
