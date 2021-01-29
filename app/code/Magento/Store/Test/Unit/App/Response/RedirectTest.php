<?php
/**
 * Response redirector tests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Response;

class RedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\App\Response\Redirect
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlCoderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_sidResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_urlCoderMock = $this->createMock(\Magento\Framework\Encryption\UrlCoder::class);
        $this->_sessionMock = $this->createMock(\Magento\Framework\Session\SessionManagerInterface::class);
        $this->_sidResolverMock = $this->createMock(\Magento\Framework\Session\SidResolverInterface::class);
        $this->_urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);

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
        $testStoreMock = $this->createMock(\Magento\Store\Model\Store::class);
        $testStoreMock->expects($this->any())->method('getBaseUrl')->willReturn($baseUrl);
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(null);
        $this->_storeManagerMock->expects($this->any())->method('getStore')
            ->willReturn($testStoreMock);
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
