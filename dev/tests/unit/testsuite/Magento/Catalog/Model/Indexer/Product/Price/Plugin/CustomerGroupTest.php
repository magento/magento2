<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_subjectMock = $this->getMock(
            '\Magento\Customer\Api\GroupRepositoryInterface', [], [], '', false
        );

        $indexerMock = $this->getMock(
            'Magento\Indexer\Model\Indexer',
            ['getId', 'invalidate'],
            [],
            '',
            false
        );
        $indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->will($this->returnValue($indexerMock));

        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup',
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
