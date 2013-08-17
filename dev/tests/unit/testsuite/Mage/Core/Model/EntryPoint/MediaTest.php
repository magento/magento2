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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_EntryPoint_MediaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_EntryPoint_Media
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirVerificationMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var callable
     */
    protected $_closure;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sync;

    /**
     * @var string
     */
    protected $_mediaDirectory = 'mediaDirectory';

    /**
     * @var string
     */
    protected $_relativeFileName = 'relativeFileName';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_config = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Mage_Core_Model_File_Storage_Request', array(), array(), '', false);
        $this->_closure = function () {
            return true;
        };
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');
        $this->_appState = $this->getMock('Mage_Core_Model_App_State', array(), array(  ), '', false);
        $this->_configMock = $this->getMock('Mage_Core_Model_File_Storage_Config', array(), array(), '', false);
        $this->_sync= $this->getMock('Mage_Core_Model_File_Storage_Synchronization', array(), array(), '', false);
        $this->_dirVerificationMock = $this->getMock('Mage_Core_Model_Dir_Verification', array(), array(), '', false);
        $this->_responseMock = $this->getMock('Mage_Core_Model_File_Storage_Response', array(), array(), '', false);

        $map = array(
            array('Mage_Core_Model_Dir_Verification', $this->_dirVerificationMock),
            array('Mage_Core_Model_App_State', $this->_appState),
            array('Mage_Core_Model_File_Storage_Request', $this->_requestMock),
            array('Mage_Core_Model_File_Storage_Synchronization', $this->_sync),
        );

        $this->_model = $this->getMock(
            'Mage_Core_Model_EntryPoint_Media', array('_setGlobalObjectManager'),
            array($this->_config, $this->_requestMock, $this->_closure,
            'var', $this->_mediaDirectory, 'cacheFile', $this->_relativeFileName,
            $this->_objectManagerMock, $this->_responseMock)
        );
        $this->_model->expects($this->any())->method('_setGlobalObjectManager');
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testProcessRequestDoesNothingIfApplicationIsNotInstalled()
    {
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(false));
        $this->_responseMock->expects($this->once())->method('sendNotFound');
        $this->_requestMock->expects($this->never())->method('getPathInfo');
        $this->_model->processRequest();
    }

    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided()
    {
        $this->_model = $this->getMock(
            'Mage_Core_Model_EntryPoint_Media', array('_setGlobalObjectManager'),
            array($this->_config, $this->_requestMock, $this->_closure, 'var', false, 'cacheFile', 'relativeFileName',
                  $this->_objectManagerMock, $this->_responseMock));
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_objectManagerMock->expects($this->once())->method('create')->with('Mage_Core_Model_File_Storage_Config')
             ->will($this->returnValue($this->_configMock));
        $this->_configMock->expects($this->once())->method('save');
        $this->_model->processRequest();

    }

    public function testProcessRequestReturnsNotFoundResponseIfResourceIsNotAllowed()
    {
        $this->_closure = function () {
            return false;
        };
        $this->_model = $this->getMock(
            'Mage_Core_Model_EntryPoint_Media', array('_setGlobalObjectManager'),
            array($this->_config, $this->_requestMock, $this->_closure, 'var', false, 'cacheFile', 'relativeFileName',
                $this->_objectManagerMock, $this->_responseMock));
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_responseMock->expects($this->once())->method('sendNotFound');
        $this->_requestMock->expects($this->once())->method('getPathInfo');
        $this->_objectManagerMock->expects($this->once())->method('create')->with('Mage_Core_Model_File_Storage_Config')
            ->will($this->returnValue($this->_configMock));
        $this->_model->processRequest();

    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotAllowed()
    {
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_configMock->expects($this->never())->method('save');
        $this->_requestMock->expects($this->once())->method('getPathInfo');
        $this->_responseMock->expects($this->once())->method('sendNotFound');
        $this->_requestMock->expects($this->never())->method('getFilePath');
        $this->_model->processRequest();
    }

    public function testProcessRequestReturnsFileIfItsProperlySynchronized()
    {
        $filePath = __DIR__ . DS . '_files';
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_requestMock->expects($this->any())->method('getPathInfo')
              ->will($this->returnValue($this->_mediaDirectory . '/'));
        $this->_sync->expects($this->once())->method('synchronize');
        $this->_requestMock->expects($this->any())
             ->method('getFilePath')->will($this->returnValue(realpath($filePath)));
        $this->_responseMock->expects($this->once())->method('sendFile')->with($filePath);
        $this->_responseMock->expects($this->never())->method('sendNotFound');
        $this->_model->processRequest();
    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotSynchronized()
    {
        $this->_appState->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_requestMock->expects($this->any())
             ->method('getPathInfo')->will($this->returnValue($this->_mediaDirectory . '/'));
        $this->_sync->expects($this->once())->method('synchronize');
        $this->_requestMock->expects($this->any())
            ->method('getFilePath')->will($this->returnValue('non_existing_file_name'));
        $this->_responseMock->expects($this->never())->method('sendFile');
        $this->_responseMock->expects($this->once())->method('sendNotFound');
        $this->_model->processRequest();
    }
}
