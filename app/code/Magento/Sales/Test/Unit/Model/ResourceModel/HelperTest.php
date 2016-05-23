<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class HelperTest
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $appResource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Helper
     */
    private $helper;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->appResource = $this->getMock(
            'Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );

        $this->resourceHelper = $this->getMock(
            'Magento\Reports\Model\ResourceModel\Helper',
            [],
            [],
            '',
            false
        );

        $this->connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );

        $this->helper = $objectManager->getObject(
            'Magento\Sales\Model\ResourceModel\Helper',
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
