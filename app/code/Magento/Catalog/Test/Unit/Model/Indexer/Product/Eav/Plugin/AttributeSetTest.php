<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter;
use Magento\Eav\Model\Entity\Attribute\Set as EavAttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AttributeSet
     */
    private $model;

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavProcessorMock;

    /**
     * @var IndexableAttributeFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var EavAttributeSet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var SetFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setFactoryMock;

    /**
     * @var EavAttributeSet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $originalSetMock;

    public function setUp()
    {
        $this->filterMock = $this->getMock(IndexableAttributeFilter::class, [], [], '', false);
        $this->subjectMock = $this->getMock(EavAttributeSet::class, [], [], '', false);
        $this->eavProcessorMock = $this->getMock(Processor::class, [], [], '', false);
        $this->setFactoryMock = $this->getMock(SetFactory::class, ['create'], [], '', false);
        $this->objectManager = new ObjectManager($this);
    }

    public function testBeforeSave()
    {
        $setId = 1;
        $this->originalSetMock = $this->getMock(EavAttributeSet::class, [], [], '', false);
        $this->originalSetMock->expects($this->once())->method('initFromSkeleton')->with($setId);

        $this->setFactoryMock->expects($this->once())->method('create')->willReturn($this->originalSetMock);
        $this->model = $this->objectManager->getObject(
            AttributeSet::class,
            [
                'indexerEavProcessor' => $this->eavProcessorMock,
                'filter' => $this->filterMock,
                'attributeSetFactory' => $this->setFactoryMock
            ]
        );

        $this->filterMock->expects($this->exactly(2))
            ->method('filter')
            ->willReturnMap(
                [
                    [$this->originalSetMock, [1, 2, 3]],
                    [$this->subjectMock, [1, 2]]
                ]
            );

        $this->subjectMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($setId);

        $this->model->beforeSave($this->subjectMock);
    }

    public function testAfterSave()
    {
        $this->eavProcessorMock->expects($this->once())->method('markIndexerAsInvalid');

        $this->model = $this->objectManager
            ->getObject(
                AttributeSet::class,
                [
                    'indexerEavProcessor' => $this->eavProcessorMock,
                    'filter' => $this->filterMock,
                    'requiresReindex' => true
                ]
            );

        $this->assertSame($this->subjectMock, $this->model->afterSave($this->subjectMock, $this->subjectMock));
    }
}
