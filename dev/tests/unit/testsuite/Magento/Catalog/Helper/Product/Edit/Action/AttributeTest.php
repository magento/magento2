<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product\Edit\Action;

/**
 * Class AttributeTest
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    protected $attribute;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false
        );

        $this->attribute = $objectManager->getObject(
            'Magento\Catalog\Helper\Product\Edit\Action\Attribute',
            [
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Run test getStoreWebsiteId method
     *
     * @return void
     */
    public function testGetStoreWebsiteId()
    {
        $storeId = 20;

        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->attribute->getStoreWebsiteId($storeId));
    }
}
