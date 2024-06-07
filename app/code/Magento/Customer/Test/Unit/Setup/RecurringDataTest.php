<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\RecurringData;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for recurring data
 */
class RecurringDataTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexer;

    /**
     * @var StateInterface|MockObject
     */
    private $state;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    /**
     * @var ModuleDataSetupInterface|MockObject
     */
    private $setup;

    /**
     * @var ModuleContextInterface|MockObject
     */
    private $context;

    /**
     * @var RecurringData
     */
    private $recurringData;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->state = $this->getMockBuilder(StateInterface::class)
            ->onlyMethods(['getStatus'])
            ->getMockForAbstractClass();
        $this->indexer = $this->getMockBuilder(IndexerInterface::class)
            ->onlyMethods(['getState', 'reindexAll'])
            ->getMockForAbstractClass();
        $this->indexer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);
        $this->indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->setup = $this->getMockBuilder(ModuleDataSetupInterface::class)
            ->onlyMethods(['tableExists'])
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder(ModuleContextInterface::class)
            ->getMockForAbstractClass();

        $this->recurringData = $this->objectManagerHelper->getObject(
            RecurringData::class,
            [
                'indexerRegistry' => $this->indexerRegistry
            ]
        );
    }

    /**
     * @param bool $isTableExists
     * @param string $indexerState
     * @param int $countReindex
     * @return void
     * @dataProvider installDataProvider
     */
    public function testInstall(bool $isTableExists, string $indexerState, int $countReindex)
    {
        $this->setup->expects($this->any())
            ->method('tableExists')
            ->with('customer_grid_flat')
            ->willReturn($isTableExists);
        $this->state->expects($this->any())
            ->method('getStatus')
            ->willReturn($indexerState);
        $this->indexer->expects($this->exactly($countReindex))
            ->method('reindexAll');
        $this->recurringData->install($this->setup, $this->context);
    }

    /**
     * @return array
     */
    public static function installDataProvider() : array
    {
        return [
            [true, StateInterface::STATUS_INVALID, 1],
            [false, StateInterface::STATUS_INVALID, 1],
            [true, StateInterface::STATUS_VALID, 0],
            [false, StateInterface::STATUS_VALID, 1],
        ];
    }
}
