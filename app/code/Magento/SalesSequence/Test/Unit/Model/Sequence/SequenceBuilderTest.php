<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\Sequence;

/**
 * Class SequenceBuilderTest
 */
class SequenceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesSequence\Model\Sequence\SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @var \Magento\SalesSequence\Model\Resource\Sequence\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\MetaFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $metaFactory;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\ProfileFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\DB\Ddl\Sequence | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sequence;

    protected function setUp()
    {
        $this->adapter = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->resourceSequenceMeta = $this->getMock(
            'Magento\SalesSequence\Model\Resource\Sequence\Meta',
            ['loadByEntityTypeAndStore', 'save', 'createSequence'],
            [],
            '',
            false
        );
        $this->meta = $this->getMock(
            'Magento\SalesSequence\Model\Sequence\Meta',
            ['getId', 'setData', 'save'],
            [],
            '',
            false
        );
        $this->sequence = $this->getMock(
            '\Magento\Framework\DB\Ddl\Sequence',
            [],
            [],
            '',
            false
        );
        $this->profile = $this->getMock(
            'Magento\SalesSequence\Model\Sequence\Profile',
            ['getId', 'setData', 'getStartValue'],
            [],
            '',
            false
        );
        $this->metaFactory = $this->getMock(
            'Magento\SalesSequence\Model\Sequence\MetaFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->metaFactory->expects($this->any())->method('create')->willReturn($this->meta);
        $this->profileFactory = $this->getMock(
            'Magento\SalesSequence\Model\Sequence\ProfileFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->profileFactory->expects($this->any())->method('create')->willReturn($this->profile);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sequenceBuilder = $helper->getObject(
            'Magento\SalesSequence\Model\Sequence\SequenceBuilder',
            [
                'resourceMetadata' => $this->resourceSequenceMeta,
                'metaFactory' => $this->metaFactory,
                'profileFactory' => $this->profileFactory
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
            ->method('getId')
            ->willReturn(1);
        $this->setExpectedException('Magento\Framework\Exception\AlreadyExistsException');
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
        $tableName = 'sequence_order_1';
        $sql = 'CREATE sequence_order_1 (sequence_value INTEGER AUTO_INCREMENT PRIMARY KEY)';
        $this->resourceSequenceMeta->expects($this->once())
            ->method('loadByEntityTypeAndStore')
            ->with($entityType, $storeId)
            ->willReturn($this->meta);
        $this->meta->expects($this->once())
            ->method('getId')
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
        $this->resourceSequenceMeta->expects($this->once())->method('createSequence')->with($sequenceTable);
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
}
