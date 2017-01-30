<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Plugin;

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    protected function setUp()
    {
        $this->_indexerMock = $this->getMock(
            '\Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup($this->_indexerMock);
    }

    /**
     * @param array $data
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave(array $data)
    {
        $subjectMock = $this->getMock('Magento\Store\Model\ResourceModel\Group', [], [], '', false);
        $objectMock = $this->getMock(
            'Magento\Framework\Model\AbstractModel',
            ['getId', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $objectMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($data['object_id']));
        $objectMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->will($this->returnValue($data['has_website_id_changed']));

        $this->_indexerMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->_model->beforeSave($subjectMock, $objectMock);
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            [
                [
                    'object_id' => 1,
                    'has_website_id_changed' => true,
                ],
            ],
            [
                [
                    'object_id' => false,
                    'has_website_id_changed' => true,
                ]
            ],
            [
                [
                    'object_id' => false,
                    'has_website_id_changed' => false,
                ]
            ],
        ];
    }
}
