<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Resolver;

use \Magento\Store\Model\Resolver\Website;

/**
 * Test class for \Magento\Store\Model\Resolver\Website
 */
class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Website
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_model = new Website($this->_storeManagerMock);
    }

    protected function tearDown(): void
    {
        unset($this->_storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->createMock(\Magento\Framework\App\ScopeInterface::class);
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }

    /**
     */
    public function testGetScopeWithInvalidScope()
    {
        $this->expectException(\Magento\Framework\Exception\State\InitException::class);

        $scopeMock = new \StdClass();
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->with(0)
            ->willReturn($scopeMock);

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }
}
