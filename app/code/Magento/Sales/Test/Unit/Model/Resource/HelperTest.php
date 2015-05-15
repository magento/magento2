<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Resource;

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
    private $adapterMock;

    /**
     * @var \Magento\Sales\Model\Resource\Helper
     */
    private $helper;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->appResource = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );

        $this->resourceHelper = $this->getMock(
            'Magento\Reports\Model\Resource\Helper',
            [],
            [],
            '',
            false
        );

        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );

        $this->helper = $objectManager->getObject(
            'Magento\Sales\Model\Resource\Helper',
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
            $this->adapterMock,
            $expectedType,
            'qty_ordered',
            $mainTable,
            $aggregationTable
        );
        $this->appResource->expects($this->once())
            ->method('getConnection')
            ->with('sales_write')
            ->willReturn($this->adapterMock);
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
