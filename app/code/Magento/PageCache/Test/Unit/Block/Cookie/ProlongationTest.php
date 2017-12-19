<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Block\Cookie;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Cookie prolongation block test class.
 * @covers \Magento\PageCache\Block\Cookie\Prolongation
 */
class ProlongationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;
    /**
     * @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheStateMock;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;
    /**
     * @var \Magento\PageCache\Block\Cookie\Prolongation
     */
    protected $_block;

    /**
     * Initial setup.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->_urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->_cacheStateMock = $this->createMock(\Magento\Framework\App\Cache\StateInterface::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_contextMock
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->_requestMock);
        $this->_contextMock
            ->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->_urlBuilderMock);
        $this->_contextMock
            ->expects($this->any())
            ->method('getCacheState')
            ->willReturn($this->_cacheStateMock);
        $this->_contextMock
            ->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->_scopeConfigMock);

        $this->_block = (new ObjectManager($this))->getObject(
            \Magento\PageCache\Block\Cookie\Prolongation::class,
            [
                'context' => $this->_contextMock
            ]
        );
    }

    /**
     * Tests getScriptOptions() method.
     *
     * @param bool   $isSecure       Is secure flag.
     * @param string $url            Action URL.
     * @param string $expectedResult Expected result.
     *
     * @return void
     *
     * @dataProvider getScriptOptionsDataProvider
     */
    public function testGetScriptOptions($isSecure, $url, $expectedResult)
    {
        $this->_requestMock
            ->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $this->_urlBuilderMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        $this->assertEquals($expectedResult, $this->_block->getScriptOptions());
    }

    /**
     * Data provider for getScriptOptions() method test.
     *
     * @return array
     */
    public function getScriptOptionsDataProvider()
    {
        return [
            'http' => [
                'isSecure' => false,
                'url' => 'http://some-name.com/page_cache/cookie/prolong',
                'expectedResult' => '{"prolongActionUrl":"http:\/\/some-name.com\/page_cache\/cookie\/prolong"}'
            ],
            'https' => [
                'isSecure' => true,
                'url' => 'https://some-name.com/page_cache/cookie/prolong',
                'expectedResult' => '{"prolongActionUrl":"https:\/\/some-name.com\/page_cache\/cookie\/prolong"}'
            ]
        ];
    }

    /**
     * Tests isAllowed() method.
     *
     * @param bool $isCacheEnabled Is cache enabled flag.
     * @param int  $cacheType      Cache type.
     * @param bool $expectedResult Expected result.
     *
     * @return void
     *
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($isCacheEnabled, $cacheType, $expectedResult)
    {
        $this->_cacheStateMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isCacheEnabled);
        $this->_scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($cacheType);

        $this->assertEquals($expectedResult, $this->_block->isAllowed());
    }

    /**
     * Data provider for isAllowed() method test.
     *
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            'enabledBuildInCache' => [
                'isCacheEnabled' => true,
                'cacheType' => \Magento\PageCache\Model\Config::BUILT_IN,
                'expectedResult' => false
            ],
            'disabledBuildInCache' => [
                'isCacheEnabled' => false,
                'cacheType' => \Magento\PageCache\Model\Config::BUILT_IN,
                'expectedResult' => false
            ],
            'enabledVarnishCache' => [
                'isCacheEnabled' => true,
                'cacheType' => \Magento\PageCache\Model\Config::VARNISH,
                'expectedResult' => true
            ],
            'disabledVarnishCache' => [
                'isCacheEnabled' => false,
                'cacheType' => \Magento\PageCache\Model\Config::VARNISH,
                'expectedResult' => false
            ],
        ];
    }
}