<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class HelperTest
 */
class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $appResource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Helper
     */
    private $helper;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->appResource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

        $this->resourceHelper = $this->createMock(\Magento\Reports\Model\ResourceModel\Helper::class);

        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);

        $this->helper = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Helper::class,
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
    public function getBestsellersReportUpdateRatingPosProvider()
    {
        return [
            ['alias', ['monthly' => 'alias', 'daily' => 'alias2', 'yearly' => 'alias3'], 'month'],
            ['alias', ['monthly' => 'alias2', 'daily' => 'alias', 'yearly' => 'alias3'], 'day'],
            ['alias', ['monthly' => 'alias2', 'daily' => 'alias2', 'yearly' => 'alias'], 'year'],
        ];
    }
}
