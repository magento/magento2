<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Setup\UpgradeData;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class UpgradeDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ModuleDataSetupInterface|MockObject
     */
    private $setupMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ModuleContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var UpgradeData
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->setupMock = $this->getMockBuilder(ModuleDataSetupInterface::class)
            ->getMockForAbstractClass();
        $this->setupMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->contextMock = $this->getMockBuilder(ModuleContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new UpgradeData();
    }

    /**
     * @param array $groupList
     * @param array $expectedCodes
     * @dataProvider upgradeDataProvider
     */
    public function testUpgradeToVersion210(array $groupList, array $expectedCodes)
    {
        $tableName = 'store_group';
        $this->setupMock->expects($this->once())
            ->method('getTable')
            ->willReturn($tableName);
        $selectMock = $this->getMockBuilder(Select::class)
            ->setMethods(['from'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('2.0.0');
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $selectMock->expects($this->once())
            ->method('from')
            ->with('store_group', ['group_id', 'name'])
            ->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchPairs')
            ->with($selectMock)
            ->willReturn($groupList);

        $i = 2;
        foreach ($expectedCodes as $groupId => $code) {
            $this->connectionMock->expects($this->at($i++))
                ->method('update')
                ->with(
                    $tableName,
                    ['code' => $code],
                    ['group_id = ?' => $groupId]
                );
        }

        $this->model->upgrade($this->setupMock, $this->contextMock);
    }

    public function upgradeDataProvider()
    {
        return [
            [
                [
                    1 => 'Test Group'
                ],
                [
                    1 => 'test_group'
                ]
            ],
            [
                [
                    1 => 'Test Group',
                    2 => 'Test Group',
                    3 => 'Test Group',
                ],
                [
                    1 => 'test_group',
                    2 => 'test_group2',
                    3 => 'test_group3'
                ]
            ],
            [
                [
                    1 => '123 Group',
                    2 => '12345',
                    3 => '123456',
                    4 => '123456',
                    5 => '12Group34',
                    6 => '&#*@#&_group'
                ],
                [
                    1 => 'group',
                    2 => 'store_group',
                    3 => 'store_group2',
                    4 => 'store_group3',
                    5 => 'group34',
                    6 => 'group2'
                ]
            ]
        ];
    }
}
