<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Helper;

/**
 * Unit test for \Magento\Search\Helper\Data
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stringMock;

    /** @var  \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    protected function setUp(): void
    {
        $this->stringMock = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = new \Magento\Search\Helper\Data(
            $this->contextMock,
            $this->stringMock,
            $this->escaperMock,
            $this->storeManagerMock
        );
    }

    public function testGetMinQueryLength()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Search\Model\Query::XML_PATH_MIN_QUERY_LENGTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($return);
        $this->assertEquals($return, $this->model->getMinQueryLength());
    }

    public function testGetMaxQueryLength()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Search\Model\Query::XML_PATH_MAX_QUERY_LENGTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($return);
        $this->assertEquals($return, $this->model->getMaxQueryLength());
    }

    /**
     * @dataProvider queryTextDataProvider
     */
    public function testGetEscapedQueryText($queryText, $maxQueryLength, $expected)
    {
        $this->requestMock->expects($this->once())->method('getParam')->willReturn($queryText);
        $this->stringMock->expects($this->any())->method('cleanString')->willReturnArgument(0);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn($maxQueryLength);
        $this->stringMock
            ->expects($this->any())
            ->method('strlen')
            ->willReturnCallback(function ($queryText) {
                return strlen($queryText);
            });
        $this->stringMock
            ->expects($this->any())
            ->method('substr')
            ->with($queryText, 0, $maxQueryLength)
            ->willReturn($expected);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->assertEquals($expected, $this->model->getEscapedQueryText());
    }

    /**
     * @return array
     */
    public function queryTextDataProvider()
    {
        return [
            ['', 100, ''],
            [null, 100, ''],
            [['test'], 100, ''],
            ['test', 100, 'test'],
            ['testtest', 7, 'testtes'],
        ];
    }

    /**
     * Test getSuggestUrl() take into consideration type of request(secure, non-secure).
     *
     * @dataProvider getSuggestUrlDataProvider
     * @param bool $isSecure
     * @return void
     */
    public function testGetSuggestUrl(bool $isSecure)
    {
        $this->requestMock->expects(self::once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $this->urlBuilderMock->expects(self::once())
            ->method('getUrl')
            ->with(self::identicalTo('search/ajax/suggest'), self::identicalTo(['_secure' => $isSecure]));
        $this->model->getSuggestUrl();
    }

    /**
     * Provide test data for testGetSuggestUrl() test.
     *
     * @return array
     */
    public function getSuggestUrlDataProvider()
    {
        return [
            'non-secure' => [
                'isSecure' => false,
            ],
            'secure' => [
                'secure' => true,
            ],
        ];
    }
}
