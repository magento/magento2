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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Di\Code\Scanner;

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
    protected $_testFiles = array();

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Di\Code\Scanner\PhpScanner(
            $this->_logMock = $this->getMock('\Magento\Tools\Di\Compiler\Log\Log', array(), array(), '', false)
        );
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->_testFiles = array($this->_testDir . '/app/code/Magento/SomeModule/Helper/Test.php');
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

        $this->assertEquals(array(), $this->_model->collectEntities($this->_testFiles));
    }
}
