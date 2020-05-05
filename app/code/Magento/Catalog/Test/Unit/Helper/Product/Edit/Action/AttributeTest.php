<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn('return-value');

        $this->assertEquals('return-value', $this->attribute->getStoreWebsiteId($storeId));
    }
}
