<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

class XmlScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\XmlScanner
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    /**
     * @var array
     */
    protected $_testFiles = [];

    protected function setUp()
    {
        $this->_model = new \Magento\Setup\Module\Di\Code\Scanner\XmlScanner(
            $this->_logMock = $this->getMock('\Magento\Setup\Module\Di\Compiler\Log\Log', [], [], '', false)
        );
        $testDir = __DIR__ . '/../../' . '/_files';
        $this->_testFiles = [
            $testDir . '/app/code/Magento/SomeModule/etc/adminhtml/system.xml',
            $testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $testDir . '/app/code/Magento/SomeModule/view/frontend/default.xml',
        ];
    }

    public function testCollectEntities()
    {
        $className = 'Magento\Store\Model\Config\Invalidator\Proxy';
        $this->_logMock->expects(
            $this->at(0)
        )->method(
            'add'
        )->with(
            4,
            $className,
            'Invalid proxy class for ' . substr($className, 0, -5)
        );
        $this->_logMock->expects(
            $this->at(1)
        )->method(
            'add'
        )->with(
            4,
            '\Magento\SomeModule\Model\Element\Proxy',
            'Invalid proxy class for ' . substr('\Magento\SomeModule\Model\Element\Proxy', 0, -5)
        );
        $this->_logMock->expects(
            $this->at(2)
        )->method(
            'add'
        )->with(
            4,
            '\Magento\SomeModule\Model\Nested\Element\Proxy',
            'Invalid proxy class for ' . substr('\Magento\SomeModule\Model\Nested\Element\Proxy', 0, -5)
        );
        $actual = $this->_model->collectEntities($this->_testFiles);
        $expected = [];
        $this->assertEquals($expected, $actual);
    }
}
