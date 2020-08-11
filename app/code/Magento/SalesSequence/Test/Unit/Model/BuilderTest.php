<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Sequence;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\Profile;
use Magento\SalesSequence\Model\ProfileFactory;
use Magento\SalesSequence\Model\ResourceModel\Meta;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var Meta|MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\Meta|MockObject
     */
    private $meta;

    /**
     * @var Profile|MockObject
     */
    private $profile;

    /**
     * @var MetaFactory|MockObject
     */
    private $metaFactory;

    /**
     * @var ProfileFactory|MockObject
     */
    private $profileFactory;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Sequence|MockObject
     */
    private $sequence;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->resourceSequenceMeta = $this->getMockBuilder(Meta::class)
            ->addMethods(['createSequence'])
            ->onlyMethods(['loadByEntityTypeAndStore', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->meta = $this->getMockBuilder(\Magento\SalesSequence\Model\Meta::class)->addMethods(['getSequenceTable'])
            ->onlyMethods(['getId', 'setData', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequence = $this->createMock(Sequence::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->profile = $this->getMockBuilder(Profile::class)
            ->addMethods(['getStartValue'])
            ->onlyMethods(['getId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaFactory = $this->createPartialMock(MetaFactory::class, ['create']);
        $this->metaFactory->expects($this->any())->method('create')->willReturn($this->meta);
        $this->profileFactory = $this->createPartialMock(
            ProfileFactory::class,
            ['create']
        );
        $this->profileFactory->expects($this->any())->method('create')->willReturn($this->profile);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('sequence_lalalka_1');

        $helper = new ObjectManager($this);
        $this->sequenceBuilder = $helper->getObject(
            Builder::class,
            [
                'resourceMetadata' => $this->resourceSequenceMeta,
                'metaFactory' => $this->metaFactory,
                'profileFactory' => $this->profileFactory,
                'appResource' => $this->resourceMock,
                'ddlSequence' => $this->sequence
            ]
        );
    }

    public function testAddSequenceExistMeta()
    {
        $entityType = 'lalalka';
        $storeId = 1;
        $this->resourceSequenceMeta->expects($this->once())
            ->method('loadByEntityTypeAndStore')
            ->with($entityType, $storeId)
            ->willReturn($this->meta);
        $this->meta->expects($this->once())
            ->method('getSequenceTable')
            ->willReturn('sequence_lalalka_1');
        $this->profileFactory->expects($this->never())
            ->method('create');
        $this->sequenceBuilder->setEntityType($entityType)
            ->setStoreId($storeId)
            ->setSuffix('SUFF')
            ->setPrefix('PREF')
            ->setStartValue(1)
            ->setStep(1)
            ->setWarningValue(9999999)
            ->setMaxValue(912992192)
            ->create();
    }

    public function testAddSequence()
    {
        $entityType = 'lalalka';
        $storeId = 1;
        $prefix = 'PRE';
        $suffix = 'SUF';
        $startValue = 1;
        $step = 1;
        $maxValue = 120000;
        $warningValue = 110000;
        $this->resourceSequenceMeta->expects($this->once())
            ->method('loadByEntityTypeAndStore')
            ->with($entityType, $storeId)
            ->willReturn($this->meta);
        $this->meta->expects($this->once())
            ->method('getSequenceTable')
            ->willReturn(null);
        $this->profileFactory->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'prefix' => $prefix,
                    'suffix' => $suffix,
                    'start_value' => $startValue,
                    'step' => $step,
                    'max_value' => $maxValue,
                    'warning_value' => $warningValue,
                    'is_active' => 1
                ]
            ])->willReturn($this->profile);
        $sequenceTable = sprintf('sequence_%s_%s', $entityType, $storeId);
        $this->metaFactory->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'entity_type' => $entityType,
                    'store_id' => $storeId,
                    'sequence_table' => $sequenceTable,
                    'active_profile' => $this->profile
                ]
            ])->willReturn($this->meta);
        $this->resourceSequenceMeta->expects($this->once())->method('save')->willReturn($this->meta);
        $this->stepCreateSequence($sequenceTable, $startValue);
        $this->sequenceBuilder->setEntityType($entityType)
            ->setStoreId($storeId)
            ->setPrefix($prefix)
            ->setSuffix($suffix)
            ->setStartValue($startValue)
            ->setStep($step)
            ->setMaxValue($maxValue)
            ->setWarningValue($warningValue)
            ->create();
    }

    /**
     * Step create sequence
     *
     * @param $sequenceName
     * @param $startNumber
     */
    private function stepCreateSequence($sequenceName, $startNumber)
    {
        $sql = "some sql";
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->connectionMock);
        $this->sequence->expects($this->once())
            ->method('getCreateSequenceDdl')
            ->with($sequenceName, $startNumber)
            ->willReturn($sql);
        $this->connectionMock->expects($this->once())->method('query')->with($sql);
    }
}
