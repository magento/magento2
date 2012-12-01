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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for Mage_Index_Model_Lock_Storage
 */
class Mage_Index_Model_Lock_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
    * Test var directory
    */
    const VAR_DIRECTORY = 'test';

    /**
     * Locks storage model
     *
     * @var Mage_Index_Model_Lock_Storage
     */
    protected $_storage;

    /**
     * Keep current process id for tests
     *
     * @var integer
     */
    protected $_currentProcessId;

    protected function setUp()
    {
        $config = $this->getMock('Mage_Core_Model_Config', array('getVarDir'), array(), '', false);
        $config->expects($this->exactly(2))
            ->method('getVarDir')
            ->will($this->returnValue(self::VAR_DIRECTORY));

        $fileModel = $this->getMock('Mage_Index_Model_Process_File',
            array(
                'setAllowCreateFolders',
                'open',
                'streamOpen',
                'streamWrite',
            )
        );

        $fileModel->expects($this->exactly(2))
            ->method('setAllowCreateFolders')
            ->with(true);
        $fileModel->expects($this->exactly(2))
            ->method('open')
            ->with(array('path' => self::VAR_DIRECTORY));
        $fileModel->expects($this->exactly(2))
            ->method('streamOpen')
            ->will($this->returnCallback(array($this, 'checkFilenameCallback')));
        $fileModel->expects($this->exactly(2))
            ->method('streamWrite')
            ->with($this->isType('string'));

        $fileFactory = $this->getMock('Mage_Index_Model_Process_FileFactory', array('createFromArray'), array(), '',
            false
        );
        $fileFactory->expects($this->exactly(2))
            ->method('createFromArray')
            ->will($this->returnValue($fileModel));

        $this->_storage = new Mage_Index_Model_Lock_Storage($config, $fileFactory);
    }

    public function testGetFile()
    {
        /**
         * List if test process IDs.
         * We need to test cases when new ID and existed ID passed into tested method.
         */
        $processIdList = array(1, 2, 2);
        foreach ($processIdList as $processId) {
            $this->_currentProcessId = $processId;
            $this->assertInstanceOf('Mage_Index_Model_Process_File', $this->_storage->getFile($processId));
        }
        $this->assertAttributeCount(2, '_fileHandlers', $this->_storage);
    }

    /**
     * Check file name
     *
     * @param string $filename
     */
    public function checkFilenameCallback($filename)
    {
        $expected = 'index_process_' . $this->_currentProcessId . '.lock';
        $this->assertEquals($expected, $filename);
    }
}
