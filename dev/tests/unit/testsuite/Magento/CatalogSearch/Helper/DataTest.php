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
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $className = 'Magento\CatalogSearch\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->_scopeConfigMock = $context->getScopeConfig();
        $this->_model = $objectManagerHelper->getObject($className, $arguments);
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
                \Magento\Framework\Store\ScopeInterface::SCOPE_STORE,
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
                \Magento\Framework\Store\ScopeInterface::SCOPE_STORE,
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
