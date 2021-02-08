<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product\Edit\Action;

/**
 * Class AttributeTest
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
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
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->attribute = $objectManager->getObject(
            \Magento\Catalog\Helper\Product\Edit\Action\Attribute::class,
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

        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn('return-value');

        $this->assertEquals('return-value', $this->attribute->getStoreWebsiteId($storeId));
    }
}
