<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Helper;

/**
 * Unit test for \Magento\Search\Helper\Data
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringMock;

    /** @var  \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->stringMock = $this->getMock(\Magento\Framework\Stdlib\StringUtils::class);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->escaperMock = $this->getMock(\Magento\Framework\Escaper::class);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);

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
            ->will($this->returnValue($return));
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
            ->will($this->returnValue($return));
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
            ->will($this->returnCallback(function ($queryText) {
                return strlen($queryText);
            }));
        $this->stringMock
            ->expects($this->any())
            ->method('substr')
            ->with($queryText, 0, $maxQueryLength)
            ->willReturn($expected);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->assertEquals($expected, $this->model->getEscapedQueryText());
    }

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
}
