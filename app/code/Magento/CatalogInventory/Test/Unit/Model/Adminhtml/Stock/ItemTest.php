<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Adminhtml\Stock;

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
            'Magento\Framework\Model\ResourceModel\AbstractResource',
            ['_construct', 'getConnection', 'getIdFieldName'],
            [],
            '',
            false
        );
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

    public function testGetIdentities()
    {
        $this->_model->setProductId(1);
        $this->assertEquals(['catalog_product_1'], $this->_model->getIdentities());
    }
}
