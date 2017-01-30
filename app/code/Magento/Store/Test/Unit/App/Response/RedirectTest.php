<?php
/**
 * Response redirector tests
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Response;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Response\Redirect
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlCoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sidResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->_storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->_urlCoderMock = $this->getMock(
            '\Magento\Framework\Encryption\UrlCoder',
            [],
            [],
            '',
            false
        );
        $this->_sessionMock = $this->getMock('\Magento\Framework\Session\SessionManagerInterface');
        $this->_sidResolverMock = $this->getMock('\Magento\Framework\Session\SidResolverInterface');
        $this->_urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface');

        $this->_model = new \Magento\Store\App\Response\Redirect(
            $this->_requestMock,
            $this->_storeManagerMock,
            $this->_urlCoderMock,
            $this->_sessionMock,
            $this->_sidResolverMock,
            $this->_urlBuilderMock
        );
    }

    /**
     * @dataProvider urlAddresses
     * @param string $baseUrl
     * @param string $successUrl
     */
    public function testSuccessUrl($baseUrl, $successUrl)
    {
        $testStoreMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $testStoreMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue($baseUrl));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(null));
        $this->_storeManagerMock->expects($this->any())->method('getStore')
            ->will($this->returnValue($testStoreMock));
        $this->assertEquals($baseUrl, $this->_model->success($successUrl));
    }

    /**
     * DataProvider with the test urls
     *
     * @return array
     */
    public function urlAddresses()
    {
        return [
            [
                'http://externalurl.com/',
                'http://internalurl.com/',
            ],
            [
                'http://internalurl.com/',
                'http://internalurl.com/'
            ]
        ];
    }
}
