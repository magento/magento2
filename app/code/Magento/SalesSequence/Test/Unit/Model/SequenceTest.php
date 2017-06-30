<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model;

use Magento\SalesSequence\Model\Sequence;

/**
 * Class SequenceTest
 */
class SequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\SalesSequence\Model\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Sequence
     */
    private $sequence;

    protected function setUp()
    {
        $this->meta = $this->getMock(
            \Magento\SalesSequence\Model\Meta::class,
            ['getSequenceTable', 'getActiveProfile'],
            [],
            '',
            false
        );
        $this->profile = $this->getMock(
            \Magento\SalesSequence\Model\Profile::class,
            ['getSuffix', 'getPrefix', 'getStep', 'getStartValue'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection'],
            [],
            '',
            false
        );
        $this->connectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['insert', 'lastInsertId']
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sequence = $helper->getObject(
            \Magento\SalesSequence\Model\Sequence::class,
            [
                'meta' => $this->meta,
                'resource' => $this->resource,
            ]
        );
    }

    public function testSequenceInitialNull()
    {
        $this->assertNull($this->sequence->getCurrentValue());
    }

    public function testSequenceNextValue()
    {
        $step = 777;
        $startValue = 3;
        $lastInsertId = 3; //at this step it will represents 777
        $this->profile->expects($this->atLeastOnce())->method('getStartValue')->willReturn($startValue);
        $this->meta->expects($this->atLeastOnce())
            ->method('getActiveProfile')
            ->willReturn(
                $this->profile
            );
        $this->meta->expects($this->atLeastOnce())
            ->method('getSequenceTable')
            ->willReturn(
                $this->sequenceParameters()->testTable
            );
        $this->connectionMock->expects($this->exactly(3))->method('insert')->with(
            $this->sequenceParameters()->testTable,
            []
        );
        $this->profile->expects($this->exactly(3))->method('getSuffix')->willReturn(
            $this->sequenceParameters()->suffix
        );
        $this->profile->expects($this->exactly(3))->method('getPrefix')->willReturn(
            $this->sequenceParameters()->prefix
        );
        $this->profile->expects($this->exactly(3))->method('getStep')->willReturn($step);
        $lastInsertId = $this->nextIncrementStep($lastInsertId, 780);
        $lastInsertId = $this->nextIncrementStep($lastInsertId, 1557);
        $this->nextIncrementStep($lastInsertId, 2334);
    }

    /**
     * @param $lastInsertId
     * @param $sequenceNumber
     * @return mixed
     */
    private function nextIncrementStep($lastInsertId, $sequenceNumber)
    {
        $lastInsertId++;
        $this->connectionMock->expects($this->at(1))->method('lastInsertId')->with(
            $this->sequenceParameters()->testTable
        )->willReturn(
            $lastInsertId
        );
        $this->assertEquals(
            sprintf(
                Sequence::DEFAULT_PATTERN,
                $this->sequenceParameters()->prefix,
                $sequenceNumber,
                $this->sequenceParameters()->suffix
            ),
            $this->sequence->getNextValue()
        );
        return $lastInsertId;
    }

    /**
     * @return \stdClass
     */
    private function sequenceParameters()
    {
        $data = new \stdClass();
        $data->prefix = 'AA-';
        $data->suffix = '-0';
        $data->testTable = 'testSequence';
        return $data;
    }
}
