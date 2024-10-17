<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Plugin;

use Magento\Email\Model\Plugin\GetUrl;
use Magento\Store\Model\Store;
use Magento\Email\Model\AbstractTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetUrlTest extends TestCase
{
    /** @var  Store|MockObject */
    private $storeMock;

    /** @var  GetUrl */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeMock = $this->createMock(Store::class);

        $this->plugin = new GetUrl();
    }

    /**
     * Test if unique store parameter passed in third argument (`$params`) of `beforeGetUrl` function.
     *
     * @return void
     */
    public function testBeforeGetUrl(): void
    {
        $storeCode = 'second_store_view';
        $params['_escape_params'] = $storeCode;
        $route = '';

        $abstractTemplateMock = $this->getMockBuilder(AbstractTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);

        $this->assertEquals(
            [$this->storeMock, $route, $params],
            $this->plugin->beforeGetUrl($abstractTemplateMock, $this->storeMock, $route, [])
        );
    }
}
