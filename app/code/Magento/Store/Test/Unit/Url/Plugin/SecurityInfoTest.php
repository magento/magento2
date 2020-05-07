<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Url\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Url\Plugin\SecurityInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecurityInfoTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var SecurityInfo
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_model = new SecurityInfo($this->_scopeConfigMock);
    }

    public function testAroundIsSecureDisabledInConfig()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                Store::XML_PATH_SECURE_IN_FRONTEND,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);
        $this->assertFalse(
            $this->_model->aroundIsSecure(
                $this->createMock(\Magento\Framework\Url\SecurityInfo::class),
                function () {
                },
                'http://example.com/account'
            )
        );
    }

    public function testAroundIsSecureEnabledInConfig()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                Store::XML_PATH_SECURE_IN_FRONTEND,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);
        $this->assertTrue(
            $this->_model->aroundIsSecure(
                $this->createMock(\Magento\Framework\Url\SecurityInfo::class),
                function () {
                    return true;
                },
                'https://example.com/account'
            )
        );
    }
}
