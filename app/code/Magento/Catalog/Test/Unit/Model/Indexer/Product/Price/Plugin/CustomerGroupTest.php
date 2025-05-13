<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CustomerGroup plugin
 */
class CustomerGroupTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerGroup
     */
    private $model;

    /**
     * @var DimensionFactory|MockObject
     */
    private $dimensionFactory;

    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainerMock;

    /**
     * @var DimensionModeConfiguration|MockObject
     */
    private $dimensionModeConfiguration;

    /**
     * @var \Callable
     */
    private $proceedMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->dimensionFactory = $this->createPartialMock(
            DimensionFactory::class,
            ['create']
        );

        $this->dimensionModeConfiguration = $this->createPartialMock(
            DimensionModeConfiguration::class,
            ['getDimensionConfiguration']
        );

        $this->tableMaintainerMock = $this->createPartialMock(
            TableMaintainer::class,
            ['createTablesForDimensions']
        );

        $this->model = $this->objectManager->getObject(
            CustomerGroup::class,
            [
                'dimensionFactory' => $this->dimensionFactory,
                'dimensionModeConfiguration' => $this->dimensionModeConfiguration,
                'tableMaintainer' => $this->tableMaintainerMock,
            ]
        );
    }

    /**
     * Check of call count createTablesForDimensions() method
     *
     * @param $customerGroupId
     * @param $callTimes
     *
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($customerGroupId, $callTimes)
    {
        $subjectMock = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $customerGroupMock = $this->createPartialMock(
            Group::class,
            ['getId']
        );
        $customerGroupMock->method('getId')->willReturn($customerGroupId);
        $this->tableMaintainerMock->expects(
            $this->exactly($callTimes)
        )->method('createTablesForDimensions');
        $this->proceedMock = function ($customerGroupMock) {
            return $customerGroupMock;
        };
        $this->dimensionModeConfiguration->method('getDimensionConfiguration')->willReturn(
            [CustomerGroupDimensionProvider::DIMENSION_NAME]
        );
        $this->model->aroundSave($subjectMock, $this->proceedMock, $customerGroupMock);
    }

    /**
     * Data provider for testAroundSave
     *
     * @return array
     */
    public static function aroundSaveDataProvider()
    {
        return [
            'customer_group_id = 0' => [
                'customerGroupId' => '0',
                'callTimes' => 0
            ],
            'customer_group_id = 1' => [
                'customerGroupId' => '1',
                'callTimes' => 0
            ],
            'customer_group_id = null' => [
                'customerGroupId' => null,
                'callTimes' => 1
            ],
        ];
    }
}
