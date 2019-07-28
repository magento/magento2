<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model;

/**
 * Class BuilderTest
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesSequence\Model\Builder
     */
    private $sequenceBuilder;

    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\MetaFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $metaFactory;

    /**
     * @var \Magento\SalesSequence\Model\ProfileFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\DB\Ddl\Sequence | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sequence;

    /**
     * @var \Magento\Framework\App\ResourceConnection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    protected function setUp()
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->resourceSequenceMeta = $this->createPartialMock(
            \Magento\SalesSequence\Model\ResourceModel\Meta::class,
            ['loadByEntityTypeAndStore', 'save', 'createSequence']
        );
        $this->meta = $this->createPartialMock(
            \Magento\SalesSequence\Model\Meta::class,
            ['getId', 'setData', 'save', 'getSequenceTable']
        );
        $this->sequence = $this->createMock(\Magento\Framework\DB\Ddl\Sequence::class);
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->profile = $this->createPartialMock(
            \Magento\SalesSequence\Model\Profile::class,
            ['getId', 'setData', 'getStartValue']
        );
        $this->metaFactory = $this->createPartialMock(\Magento\SalesSequence\Model\MetaFactory::class, ['create']);
        $this->metaFactory->expects($this->any())->method('create')->willReturn($this->meta);
        $this->profileFactory = $this->createPartialMock(
            \Magento\SalesSequence\Model\ProfileFactory::class,
            ['create']
        );
        $this->profileFactory->expects($this->any())->method('create')->willReturn($this->profile);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('sequence_lalalka_1');

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sequenceBuilder = $helper->getObject(
            \Magento\SalesSequence\Model\Builder::class,
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
