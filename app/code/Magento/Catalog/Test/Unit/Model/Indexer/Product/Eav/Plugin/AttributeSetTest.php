<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeSetTest extends \PHPUnit_Framework_TestCase
{
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
	 * @var Set|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $subjectMock;

	public function setUp()
	{
		$this->filterMock = $this->getMock(IndexableAttributeFilter::class, [], [], '', false);
		$this->subjectMock = $this->getMock(Set::class, [], [], '', false);
		$this->eavProcessorMock = $this->getMock(Processor::class, [], [], '', false);
	}

	public function testBeforeSave()
	{
		$this->model = (new ObjectManager($this))
			->getObject(
				AttributeSet::class,
				[
					'indexerEavProcessor' => $this->eavProcessorMock,
					'filter' => $this->filterMock
				]
			);

		$this->filterMock->expects($this->at(0))
		                 ->method('filter')
		                 ->will($this->returnValue([1, 2, 3]));
		$this->filterMock->expects($this->at(1))
		                 ->method('filter')
		                 ->will($this->returnValue([1, 2]));

		$this->subjectMock->expects($this->exactly(2))
		                  ->method('getId')
		                  ->will($this->returnValue(1));

		$this->model->beforeSave($this->subjectMock);
	}

    public function testAfterSave()
    {
    	$this->eavProcessorMock->expects($this->once())->method('markIndexerAsInvalid');

	    $this->model = (new ObjectManager($this))
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
