<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Scanner;

require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Helper/Test.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/ElementFactory.php';
class PhpScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Code\Scanner\PhpScanner
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var array
     */
    protected $_testFiles = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Di\Code\Scanner\PhpScanner(
            $this->_logMock = $this->getMock('\Magento\Tools\Di\Compiler\Log\Log', [], [], '', false)
        );
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->_testFiles = [$this->_testDir . '/app/code/Magento/SomeModule/Helper/Test.php'];
    }

    public function testCollectEntities()
    {
        $this->_logMock->expects(
            $this->at(0)
        )->method(
            'add'
        )->with(
            4,
            'Magento\SomeModule\Module\Factory',
            'Invalid Factory for nonexistent class Magento\SomeModule\Module in file ' . $this->_testFiles[0]
        );
        $this->_logMock->expects(
            $this->at(1)
        )->method(
            'add'
        )->with(
            4,
            'Magento\SomeModule\Element\Factory',
            'Invalid Factory declaration for class Magento\SomeModule\Element in file ' . $this->_testFiles[0]
        );

        $this->assertEquals([], $this->_model->collectEntities($this->_testFiles));
    }
}
