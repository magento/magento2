<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Reader;

use Magento\Framework\App\Config\ScopeConfigInterface;

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
            'Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory',
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
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
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
                    new \Magento\Framework\DataObject(['path' => 'config/key1', 'value' => 'default_db_value1']),
                    new \Magento\Framework\DataObject(['path' => 'config/key3', 'value' => 'default_db_value3']),
                ]
            )
        );
        $expectedData = [
            'config' => ['key1' => 'default_db_value1', 'key2' => 'default_value2', 'key3' => 'default_db_value3'],
        ];
        $this->assertEquals($expectedData, $this->_model->read());
    }
}
