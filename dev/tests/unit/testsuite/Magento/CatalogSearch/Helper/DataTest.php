<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    public function setUp()
    {
        $this->_contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->_stringMock = $this->getMock('Magento\Framework\Stdlib\String');
        $this->_queryFactoryMock = $this->getMock('Magento\CatalogSearch\Model\QueryFactory', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_escaperMock = $this->getMock('Magento\Framework\Escaper');
        $this->_filterManagerMock = $this->getMock('Magento\Framework\Filter\FilterManager', [], [], '', false);
        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');

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
                \Magento\CatalogSearch\Model\Query::XML_PATH_MIN_QUERY_LENGTH,
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
                \Magento\CatalogSearch\Model\Query::XML_PATH_MAX_QUERY_LENGTH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->will($this->returnValue($return));
        $this->assertEquals($return, $this->_model->getMaxQueryLength());
    }

    public function testGetMaxQueryWords()
    {
        $return = 'some_value';
        $this->_scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\CatalogSearch\Model\Query::XML_PATH_MAX_QUERY_WORDS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->will($this->returnValue($return));
        $this->assertEquals($return, $this->_model->getMaxQueryWords());
    }

    /**
     * @return array
     */
    public function prepareIndexdataDataProvider()
    {
        return array(
            array(
                null,
                array(
                    'index' => array(),
                    'separator' => '--'
                )
            ),
            array(
                'element1--element2--element3--element4',
                array(
                    'index' => array(
                        'element1',
                        'element2',
                        array(
                            'element3',
                            'element4',
                        )
                    ),
                    'separator' => '--'
                )
            )
        );
    }
}
