<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Meta;
use Magento\SalesSequence\Model\Profile;
use Magento\SalesSequence\Model\Sequence;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SequenceTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var Profile|MockObject
     */
    private $profile;

    /**
     * @var Meta|MockObject
     */
    private $meta;

    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->meta = $this->getMockBuilder(Meta::class)
            ->addMethods(['getSequenceTable', 'getActiveProfile'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->profile = $this->getMockBuilder(Profile::class)
            ->addMethods(['getSuffix', 'getPrefix', 'getStep', 'getStartValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->createPartialMock(ResourceConnection::class, ['getConnection']);
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['insert', 'lastInsertId']
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $helper = new ObjectManager($this);
        $this->sequence = $helper->getObject(
            Sequence::class,
            [
                'meta' => $this->meta,
                'resource' => $this->resource
            ]
        );
    }

    /**
     * @return void
     */
    public function testSequenceInitialNull(): void
    {
        $this->assertNull($this->sequence->getCurrentValue());
    }

    /**
     * @return void
     */
    public function testSequenceNextValue(): void
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
        $withArgs = $willReturnArgs = [];

        $withArgs[] = [$this->sequenceParameters()->testTable];
        $willReturnArgs[] = ++$lastInsertId;

        $withArgs[] = [$this->sequenceParameters()->testTable];
        $willReturnArgs[] = ++$lastInsertId;

        $withArgs[] = [$this->sequenceParameters()->testTable];
        $willReturnArgs[] = ++$lastInsertId;

        $this->connectionMock
            ->method('lastInsertId')
            ->willReturnCallback(function (...$withArgs) use ($willReturnArgs) {
                if (!empty($withArgs)) {
                    static $callCount = 0;
                    $returnValue = $willReturnArgs[$callCount];
                    $callCount++;
                    return $returnValue;
                }
            });

        $this->nextIncrementStep(780);
        $this->nextIncrementStep(1557);
        $this->nextIncrementStep(2334);
    }

    /**
     * @param $sequenceNumber
     */
    private function nextIncrementStep($sequenceNumber)
    {
        $this->assertEquals(
            sprintf(
                Sequence::DEFAULT_PATTERN,
                $this->sequenceParameters()->prefix,
                $sequenceNumber,
                $this->sequenceParameters()->suffix
            ),
            $this->sequence->getNextValue()
        );
    }

    /**
     * @return \stdClass
     */
    private function sequenceParameters(): \stdClass
    {
        $data = new \stdClass();
        $data->prefix = 'AA-';
        $data->suffix = '-0';
        $data->testTable = 'testSequence';
        return $data;
    }
}
