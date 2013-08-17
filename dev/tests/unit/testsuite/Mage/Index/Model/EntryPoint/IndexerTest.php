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
class Mage_Index_Model_EntryPoint_IndexerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Index_Model_EntryPoint_Indexer
     */
    protected $_entryPoint;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_primaryConfig;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_reportDir;

    protected function setUp()
    {
        $this->_reportDir = 'tmp' . DIRECTORY_SEPARATOR . 'reports';
        $this->_primaryConfig = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_entryPoint = $this->getMock(
            'Mage_Index_Model_EntryPoint_Indexer',
            array('_setGlobalObjectManager'),
            array($this->_reportDir, $this->_filesystem, $this->_primaryConfig, $this->_objectManager)
        );
    }

    public function testProcessRequest()
    {
        $dirVerification = $this->getMock('Mage_Core_Model_Dir_Verification', array(), array(), '', false);
        $dirVerification->expects($this->once())->method('createAndVerifyDirectories');

        $process = $this->getMock('Mage_Index_Model_Process', array(), array(), '', false);
        $processIndexer = $this->getMockForAbstractClass(
            'Mage_Index_Model_Indexer_Abstract',
            array(),
            '',
            false
        );
        $processIndexer->expects($this->any())->method('isVisible')->will($this->returnValue(true));
        $process->expects($this->any())->method('getIndexer')->will($this->returnValue($processIndexer));
        $process->expects($this->once())->method('reindexEverything')->will($this->returnSelf());

        $indexer = $this->getMock('Mage_Index_Model_Indexer', array(), array(), '', false);
        $indexer->expects($this->once())
            ->method('getProcessesCollection')
            ->will($this->returnValue(array($process)));

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
                    array('Mage_Index_Model_Indexer', array(), $indexer),
                )
            ));
        // check that report directory is cleaned
        $this->_filesystem->expects($this->once())
            ->method('delete')
            ->with($this->_reportDir, dirname($this->_reportDir));

        $this->_entryPoint->processRequest();
    }
}
