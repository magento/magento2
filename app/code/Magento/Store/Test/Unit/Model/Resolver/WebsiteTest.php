<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Resolver;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\Resolver\Website;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Store\Model\Resolver\Website
 */
class WebsiteTest extends TestCase
{
    /**
     * @var Website
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->_model = new Website($this->_storeManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->_storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }

    public function testGetScopeWithInvalidScope()
    {
        $this->expectException(InitException::class);
        $scopeMock = new \StdClass();
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }
}
