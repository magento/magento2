<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader;

class DefaultReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Reader\DefaultReader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    protected function setUp()
    {
        $this->_initialConfigMock = $this->getMock('Magento\Framework\App\Config\Initial', [], [], '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\Store\Model\Resource\Config\Collection\ScopedFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Store\Model\Config\Reader\DefaultReader(
            $this->_initialConfigMock,
            new \Magento\Framework\App\Config\Scope\Converter(),
            $this->_collectionFactory
        );
    }

    public function testRead()
    {
        $this->_initialConfigMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        )->will(
            $this->returnValue(['config' => ['key1' => 'default_value1', 'key2' => 'default_value2']])
        );
        $this->_collectionFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['scope' => 'default']
        )->will(
            $this->returnValue(
                [
                    new \Magento\Framework\Object(['path' => 'config/key1', 'value' => 'default_db_value1']),
                    new \Magento\Framework\Object(['path' => 'config/key3', 'value' => 'default_db_value3']),
                ]
            )
        );
        $expectedData = [
            'config' => ['key1' => 'default_db_value1', 'key2' => 'default_value2', 'key3' => 'default_db_value3'],
        ];
        $this->assertEquals($expectedData, $this->_model->read());
    }
}
