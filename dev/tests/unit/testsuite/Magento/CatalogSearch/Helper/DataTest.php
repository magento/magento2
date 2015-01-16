<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Helper;

/**
 * Unit test for \Magento\CatalogSearch\Helper\Data
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stringMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filterManagerMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    public function setUp()
    {
        $this->_contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->_stringMock = $this->getMock('Magento\Framework\Stdlib\String');
        $this->_queryFactoryMock = $this->getMock('Magento\Search\Model\QueryFactory', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_escaperMock = $this->getMock('Magento\Framework\Escaper');
        $this->_filterManagerMock = $this->getMock('Magento\Framework\Filter\FilterManager', [], [], '', false);
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $this->_model = new \Magento\CatalogSearch\Helper\Data(
            $this->_contextMock,
            $this->_stringMock,
            $this->_scopeConfigMock,
            $this->_queryFactoryMock,
            $this->_escaperMock,
            $this->_filterManagerMock,
            $this->_storeManagerMock
        );
    }

    /**
     * @param null|string $expected
     * @param array $data
     * @dataProvider prepareIndexdataDataProvider
     */
    public function testPrepareIndexdata($expected, array $data)
    {
        $this->assertEquals($expected, $this->_model->prepareIndexdata($data['index'], $data['separator']));
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

    /**
     * @return array
     */
    public function prepareIndexdataDataProvider()
    {
        return [
            [
                null,
                [
                    'index' => [],
                    'separator' => '--'
                ],
            ],
            [
                'element1--element2--element3--element4',
                [
                    'index' => [
                        'element1',
                        'element2',
                        [
                            'element3',
                            'element4',
                        ],
                    ],
                    'separator' => '--'
                ]
            ]
        ];
    }
}
