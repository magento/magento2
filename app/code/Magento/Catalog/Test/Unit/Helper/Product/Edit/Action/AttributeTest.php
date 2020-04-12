<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product\Edit\Action;

use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->attribute = $objectManager->getObject(
            Attribute::class,
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

        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId']);

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
