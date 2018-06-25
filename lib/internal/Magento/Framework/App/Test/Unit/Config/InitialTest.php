<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCacheMock;

    protected function setUp()
    {
        $this->_initialReaderMock =
            $this->getMock('Magento\Framework\App\Config\Initial\Reader', [], [], '', false);
        $this->_configCacheMock =
            $this->getMock('Magento\Framework\App\Cache\Type\Config', [], [], '', false);
        $serializedData = serialize(
            [
                'data' => [
                    'default' => ['key' => 'default_value'],
                    'stores' => ['default' => ['key' => 'store_value']],
                    'websites' => ['default' => ['key' => 'website_value']],
                ],
                'metadata' => ['metadata'],
            ]
        );
        $this->_configCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            'initial_config'
        )->will(
            $this->returnValue($serializedData)
        );

        $this->_model = new \Magento\Framework\App\Config\Initial($this->_initialReaderMock, $this->_configCacheMock);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param string $scope
     * @param array $expectedResult
     */
    public function testGetData($scope, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->getData($scope));
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            ['default', ['key' => 'default_value']],
            ['stores|default', ['key' => 'store_value']],
            ['websites|default', ['key' => 'website_value']]
        ];
    }

    public function testGetMetadata()
    {
        $expectedResult = ['metadata'];
        $this->assertEquals($expectedResult, $this->_model->getMetadata());
    }
}
