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

class AttributeSetTest extends \PHPUnit\Framework\TestCase
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
     * @var Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eavProcessorMock;

    /**
     * @var IndexableAttributeFilter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterMock;

    /**
     * @var EavAttributeSet|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    /**
     * @var SetFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $setFactoryMock;

    /**
     * @var EavAttributeSet|\PHPUnit\Framework\MockObject\MockObject
     */
    private $originalSetMock;

    protected function setUp(): void
    {
        $this->filterMock = $this->createMock(IndexableAttributeFilter::class);
        $this->subjectMock = $this->createMock(EavAttributeSet::class);
        $this->eavProcessorMock = $this->createMock(Processor::class);
        $this->setFactoryMock = $this->createPartialMock(SetFactory::class, ['create']);
        $this->objectManager = new ObjectManager($this);
    }

    public function testBeforeSave()
    {
        $setId = 1;
        $this->originalSetMock = $this->createMock(EavAttributeSet::class);
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
