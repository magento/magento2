<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Adminhtml\Stock;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Adminhtml\Stock\Item|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * setUp
     */
    protected function setUp()
    {
        $resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            ['_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName'],
            [],
            '',
            false
        );
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $groupManagement = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->setMethods(['getAllCustomersGroup'])
            ->getMockForAbstractClass();

        $allGroup = $this->getMockBuilder('Magento\Customer\Api\Data\GroupInterface')
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $allGroup->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(32000));

        $groupManagement->expects($this->any())
            ->method('getAllCustomersGroup')
            ->will($this->returnValue($allGroup));

        $this->_model = $objectHelper->getObject(
            '\Magento\CatalogInventory\Model\Adminhtml\Stock\Item',
            [
                'resource' => $resourceMock,
                'groupManagement' => $groupManagement
            ]
        );
    }

    public function testGetCustomerGroupId()
    {
        $this->_model->setCustomerGroupId(null);
        $this->assertEquals(32000, $this->_model->getCustomerGroupId());
        $this->_model->setCustomerGroupId(2);
        $this->assertEquals(2, $this->_model->getCustomerGroupId());
    }
}
