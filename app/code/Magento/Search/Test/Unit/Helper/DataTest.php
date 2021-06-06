<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Search\Helper\Data;
use Magento\Search\Model\Query;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Search\Helper\Data
 */
class DataTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var StringUtils|MockObject
     */
    protected $stringMock;

    /** @var  RequestInterface|MockObject */
    protected $requestMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    protected function setUp(): void
    {
        $this->stringMock = $this->createMock(StringUtils::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = new Data(
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
                Query::XML_PATH_MIN_QUERY_LENGTH,
                ScopeInterface::SCOPE_STORE,
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
                Query::XML_PATH_MAX_QUERY_LENGTH,
                ScopeInterface::SCOPE_STORE,
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
