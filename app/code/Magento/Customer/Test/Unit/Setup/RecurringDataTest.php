<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Setup;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\RecurringData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for recurring data
 */
class RecurringDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var IndexerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexer;

    /**
     * @var StateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $state;

    /**
     * @var IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerRegistry;

    /**
     * @var ModuleDataSetupInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $setup;

    /**
     * @var ModuleContextInterface|\PHPUnit\Framework\MockObject\MockObject
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
            ->setMethods(['getStatus'])
            ->getMockForAbstractClass();
        $this->indexer = $this->getMockBuilder(IndexerInterface::class)
            ->setMethods(['getState', 'reindexAll'])
            ->getMockForAbstractClass();
        $this->indexer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);
        $this->indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->setup = $this->getMockBuilder(ModuleDataSetupInterface::class)
            ->setMethods(['tableExists'])
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
    public function installDataProvider() : array
    {
        return [
            [true, StateInterface::STATUS_INVALID, 1],
            [false, StateInterface::STATUS_INVALID, 1],
            [true, StateInterface::STATUS_VALID, 0],
            [false, StateInterface::STATUS_VALID, 1],
        ];
    }
}
