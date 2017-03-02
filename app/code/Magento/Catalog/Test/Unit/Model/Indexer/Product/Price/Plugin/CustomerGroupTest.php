<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Plugin;

class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup
     */
    protected $_model;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_subjectMock = $this->getMock(
            \Magento\Customer\Api\GroupRepositoryInterface::class, [], [], '', false
        );

        $indexerMock = $this->getMock(
            \Magento\Indexer\Model\Indexer::class,
            ['getId', 'invalidate'],
            [],
            '',
            false
        );
        $indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock = $this->getMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->will($this->returnValue($indexerMock));

        $this->_model = $this->_objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    public function testAroundDelete()
    {
        $this->assertEquals('return_value', $this->_model->afterDelete($this->_subjectMock, 'return_value'));
    }

    public function testAroundDeleteById()
    {
        $this->assertEquals('return_value', $this->_model->afterDeleteById($this->_subjectMock, 'return_value'));
    }

    public function testAroundSave()
    {
        $this->assertEquals('return_value', $this->_model->afterSave($this->_subjectMock, 'return_value'));
    }
}
