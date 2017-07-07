<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            ['_construct', 'getConnection', 'getIdFieldName'],
            [],
            '',
            false
        );
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->setMethods(['getAllCustomersGroup'])
            ->getMockForAbstractClass();

        $allGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $allGroup->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(32000));

        $groupManagement->expects($this->any())
            ->method('getAllCustomersGroup')
            ->will($this->returnValue($allGroup));

        $this->_model = $objectHelper->getObject(
            \Magento\CatalogInventory\Model\Adminhtml\Stock\Item::class,
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
        $this->assertEquals(['cat_p_1'], $this->_model->getIdentities());
    }
}
