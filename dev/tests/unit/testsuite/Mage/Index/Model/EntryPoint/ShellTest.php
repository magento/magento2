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
 * @category    Magento
 * @package     Mage_Index
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Index_Model_EntryPoint_ShellTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Index_Model_EntryPoint_Shell
     */
    protected $_entryPoint;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shellErrorHandler;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_primaryConfig;

    protected function setUp()
    {
        $this->_primaryConfig = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_shellErrorHandler = $this->getMock(
            'Mage_Index_Model_EntryPoint_Shell_ErrorHandler',
            array(),
            array(),
            '',
            false
        );
        $this->_entryPoint = $this->getMock(
            'Mage_Index_Model_EntryPoint_Shell',
            array('_setGlobalObjectManager'),
            array('indexer.php', $this->_shellErrorHandler, $this->_primaryConfig, $this->_objectManager)
        );
    }

    /**
     * @param boolean $shellHasErrors
     * @dataProvider processRequestDataProvider
     */
    public function testProcessRequest($shellHasErrors)
    {
        $dirVerification = $this->getMock('Mage_Core_Model_Dir_Verification', array(), array(), '', false);
        $dirVerification->expects($this->once())->method('createAndVerifyDirectories');
        $shell = $this->getMock('Mage_Index_Model_Shell', array(), array(), '', false);
        $shell->expects($this->once())
            ->method('hasErrors')
            ->will($this->returnValue($shellHasErrors));
        $shell->expects($this->once())
            ->method('run');

        if ($shellHasErrors) {
            $this->_shellErrorHandler->expects($this->once())
                ->method('terminate')
                ->with(1);
        }

        // configure object manager
        $this->_objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(
                array(
                    array('Mage_Core_Model_Dir_Verification', $dirVerification),
                )
            ));
        $this->_objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap(
                array(
                    array('Mage_Index_Model_Shell', array('entryPoint' => 'indexer.php'), $shell),
                )
            ));

        $this->_entryPoint->processRequest();
    }

    /**
     * @return array
     */
    public function processRequestDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }
}
