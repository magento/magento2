<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\SalesRule\Model\Rule\RuleQuoteRecollectTotalsOnDemand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleQuoteRecollectTotalsOnDemandTest extends TestCase
{
    /**
     * @var Quote|MockObject
     */
    private $resourceModel;

    /**
     * @var RuleQuoteRecollectTotalsOnDemand
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceModel = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'getMainTable'])
            ->getMockForAbstractClass();
        $this->model = new RuleQuoteRecollectTotalsOnDemand($this->resourceModel);
    }

    /**
     * Test that multiple updates query are executed on large result
     *
     * @return void
     */
    public function testExecute(): void
    {
        $ruleId = 1;
        $mainTableName = 'quote';
        $selectRange1 = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['from', 'where', 'order', 'limit'])
            ->getMockForAbstractClass();
        $selectRange2 = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['from', 'where', 'order', 'limit'])
            ->getMockForAbstractClass();
        $selectRange1->method('from')
            ->willReturnSelf();
        $selectRange1->method('where')
            ->withConsecutive(
                ['is_active = ?', 1],
                ['FIND_IN_SET(?, applied_rule_ids)', $ruleId],
                ['entity_id > ?', 0],
            )
            ->willReturnSelf();
        $selectRange1->method('order')
            ->with('entity_id ' . Select::SQL_ASC)
            ->willReturnSelf();
        $selectRange1->method('limit')
            ->with(10000)
            ->willReturnSelf();
        $selectRange2->method('from')
            ->willReturnSelf();
        $selectRange2->method('where')
            ->withConsecutive(
                ['is_active = ?', 1],
                ['FIND_IN_SET(?, applied_rule_ids)', $ruleId],
                ['entity_id > ?', 10000],
            )
            ->willReturnSelf();
        $selectRange2->method('order')
            ->with('entity_id ' . Select::SQL_ASC)
            ->willReturnSelf();
        $selectRange2->method('limit')
            ->with(10000)
            ->willReturnSelf();
        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['select', 'fetchCol', 'update'])
            ->getMockForAbstractClass();
        $connection->expects($this->exactly(2))
            ->method('select')
            ->willReturnOnConsecutiveCalls($selectRange1, $selectRange2);
        $connection->expects($this->exactly(2))
            ->method('fetchCol')
            ->willReturn(range(1, 10000), range(10001, 18999));
        $connection->expects($this->exactly(19))
            ->method('update')
            ->withConsecutive(
                ...array_map(
                    static function (int $iteration) use ($mainTableName) {
                        return [
                            $mainTableName,
                            ['trigger_recollect' => 1],
                            [
                                'entity_id IN (?)' => range(
                                    $iteration * 1000 + 1,
                                    min(18999, ($iteration * 1000 + 1000))
                                ),
                            ]
                        ];
                    },
                    range(0, 18)
                )
            );
        $this->resourceModel->method('getConnection')
            ->willReturn($connection);
        $this->resourceModel->method('getMainTable')
            ->willReturn($mainTableName);

        $this->model->execute($ruleId);
    }
}
