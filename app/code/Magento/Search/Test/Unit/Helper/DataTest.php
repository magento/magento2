<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $_model;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stringMock;

    /**
     * @var \Magento\Search\Model\QueryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_queryFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_escaperMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    public function setUp()
    {
        $this->_stringMock = $this->getMock('Magento\Framework\Stdlib\StringUtils');
        $this->_queryFactoryMock = $this->getMock('Magento\Search\Model\QueryFactory', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_escaperMock = $this->getMock('Magento\Framework\Escaper');
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->_contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->_contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->_scopeConfigMock);

        $this->_model = new \Magento\Search\Helper\Data(
            $this->_contextMock,
            $this->_stringMock,
            $this->_queryFactoryMock,
            $this->_escaperMock,
            $this->_storeManagerMock
        );
    }

    public function testGetMinQueryLength()
    {
        $return = 'some_value';
        $this->_scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Search\Model\Query::XML_PATH_MIN_QUERY_LENGTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->will($this->returnValue($return));
        $this->assertEquals($return, $this->_model->getMinQueryLength());
    }

    public function testGetMaxQueryLength()
    {
        $return = 'some_value';
        $this->_scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Search\Model\Query::XML_PATH_MAX_QUERY_LENGTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->will($this->returnValue($return));
        $this->assertEquals($return, $this->_model->getMaxQueryLength());
    }
}
