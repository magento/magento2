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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/Di/Code/Scanner/ScannerInterface.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/Di/Code/Scanner/FileScanner.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/Di/Code/Scanner/PhpScanner.php';

class Magento_Tools_Di_Code_Scanner_PhpScannerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento\Tools\Di\Code\Scanner\PhpScanner
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

    protected function setUp()
    {
        $this->_model = new Magento\Tools\Di\Code\Scanner\PhpScanner();
        $this->_testDir = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../') . '/_files');
        $this->_testFiles = array(
            $this->_testDir . '/app/code/Mage/SomeModule/Helper/Test.php',
            $this->_testDir . '/app/code/Mage/SomeModule/Model/Test.php',
            $this->_testDir . '/app/bootstrap.php',
        );
    }

    public function testCollectEntities()
    {
        $actual = $this->_model->collectEntities($this->_testFiles);
        $expected = array(
            'Mage_SomeModule_ElementFactory',
            'Mage_SomeModule_BlockFactory',
            'Mage_SomeModule_ModelFactory',
            'Mage_SomeModule_Model_BlockFactory',
            'Mage_Bootstrap_ModelFactory',
        );
        $this->assertEquals($expected, $actual);
    }
}
