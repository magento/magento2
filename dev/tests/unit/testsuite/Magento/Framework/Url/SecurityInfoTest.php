<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

class SecurityInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Framework\Url\SecurityInfo
     */
    protected $_model;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = new \Magento\Framework\Url\SecurityInfo($this->_scopeConfigMock, ['/account', '/cart']);
    }

    public function testIsSecureReturnsFalseIfDisabledInConfig()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(false));
        $this->assertFalse($this->_model->isSecure('http://example.com/account'));
    }

    /**
     * @param string $url
     * @param bool $expected
     * @dataProvider secureUrlDataProvider
     */
    public function testIsSecureChecksIfUrlIsInSecureList($url, $expected)
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $this->assertEquals($expected, $this->_model->isSecure($url));
    }

    public function secureUrlDataProvider()
    {
        return [
            ['/account', true],
            ['/product', false],
            ['/product/12312', false],
            ['/cart', true],
            ['/cart/add', true]
        ];
    }
}
