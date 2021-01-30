<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout\Billing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Multishipping\Block\Checkout\Billing\Items;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemsTest extends TestCase
{
    /**
     * @var Items
     */
    private $model;

    /**
     * @var MockObject
     */
    private $urlBuilderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->model = $objectManager->getObject(
            Items::class,
            [
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testGetVirtualProductEditUrl()
    {
        $url = 'http://example.com';
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart', [])->willReturn($url);
        $this->assertEquals($url, $this->model->getVirtualProductEditUrl());
    }
}
