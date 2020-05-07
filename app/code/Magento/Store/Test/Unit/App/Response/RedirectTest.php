<?php
/**
 * Response redirector tests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Response;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\App\Response\Redirect;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    /**
     * @var Redirect
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_urlCoderMock;

    /**
     * @var MockObject
     */
    protected $_sessionMock;

    /**
     * @var MockObject
     */
    protected $_sidResolverMock;

    /**
     * @var MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->_urlCoderMock = $this->createMock(UrlCoder::class);
        $this->_sessionMock = $this->getMockForAbstractClass(SessionManagerInterface::class);
        $this->_sidResolverMock = $this->getMockForAbstractClass(SidResolverInterface::class);
        $this->_urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $this->_model = new Redirect(
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
        $testStoreMock = $this->createMock(Store::class);
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
