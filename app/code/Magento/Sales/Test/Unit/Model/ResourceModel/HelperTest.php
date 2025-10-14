<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\ResourceModel\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $resourceHelper;

    /**
     * @var MockObject
     */
    private $appResource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->appResource = $this->createMock(ResourceConnection::class);

        $this->resourceHelper = $this->createMock(\Magento\Reports\Model\ResourceModel\Helper::class);

        $this->connectionMock = $this->createMock(Mysql::class);

        $this->helper = $objectManager->getObject(
            Helper::class,
            [
                'resource' => $this->appResource,
                'reportsResourceHelper' => $this->resourceHelper
            ]
        );
    }

    /**
     * @param string $aggregation
     * @param array $aggregationAliases
     * @param string $expectedType
     *
     * @dataProvider getBestsellersReportUpdateRatingPosProvider
     */
    public function testGetBestsellersReportUpdateRatingPos($aggregation, $aggregationAliases, $expectedType)
    {
        $mainTable = 'main_table';
        $aggregationTable = 'aggregation_table';
        $this->resourceHelper->expects($this->once())->method('updateReportRatingPos')->with(
            $this->connectionMock,
            $expectedType,
            'qty_ordered',
            $mainTable,
            $aggregationTable
        );
        $this->appResource->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->connectionMock);
        $this->helper->getBestsellersReportUpdateRatingPos(
            $aggregation,
            $aggregationAliases,
            $mainTable,
            $aggregationTable
        );
    }

    /**
     * @return array
     */
    public static function getBestsellersReportUpdateRatingPosProvider()
    {
        return [
            ['alias', ['monthly' => 'alias', 'daily' => 'alias2', 'yearly' => 'alias3'], 'month'],
            ['alias', ['monthly' => 'alias2', 'daily' => 'alias', 'yearly' => 'alias3'], 'day'],
            ['alias', ['monthly' => 'alias2', 'daily' => 'alias2', 'yearly' => 'alias'], 'year'],
        ];
    }
}
