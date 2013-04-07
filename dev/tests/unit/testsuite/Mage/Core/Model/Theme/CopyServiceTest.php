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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Theme_CopyServiceTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * @var Mage_Core_Model_Theme_CopyService
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sourceTheme;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_targetTheme;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_targetFiles = array();

    /**
     * @var PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_sourceFiles = array();

    protected function setUp()
    {
        $sourceFileOne = $this->getMock('Mage_Core_Model_Theme_File', array('delete'), array(), '', false);
        $sourceFileOne->setData(array(
            'file_path'     => 'fixture_file_path_one',
            'file_type'     => 'fixture_file_type_one',
            'content'       => 'fixture_content_one',
            'sort_order'    => 10,
        ));
        $sourceFileTwo = $this->getMock('Mage_Core_Model_Theme_File', array('delete'), array(), '', false);
        $sourceFileTwo->setData(array(
            'file_path'     => 'fixture_file_path_two',
            'file_type'     => 'fixture_file_type_two',
            'content'       => 'fixture_content_two',
            'sort_order'    => 20,
        ));
        $this->_sourceFiles = array($sourceFileOne, $sourceFileTwo);
        $this->_sourceTheme = $this->getMock(
            'Mage_Core_Model_Theme', array('getFiles', 'getCustomizationPath'), array(), '', false
        );

        $this->_targetFiles = array(
            $this->getMock('Mage_Core_Model_Theme_File', array('delete'), array(), '', false),
            $this->getMock('Mage_Core_Model_Theme_File', array('delete'), array(), '', false),
        );
        $this->_targetTheme = $this->getMock(
            'Mage_Core_Model_Theme', array('getFiles', 'getCustomizationPath'), array(), '', false
        );
        $this->_targetTheme->setId(123);

        $this->_objectManager = $this->getMockForAbstractClass('Magento_ObjectManager');
        $this->_filesystem = $this->getMock(
            'Magento_Filesystem', array('isDirectory', 'searchKeys', 'copy'),
            array($this->getMockForAbstractClass('Magento_Filesystem_AdapterInterface'))
        );
        $this->_object = new Mage_Core_Model_Theme_CopyService($this->_objectManager, $this->_filesystem);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_filesystem = null;
        $this->_objectManager = null;
        $this->_sourceTheme = null;
        $this->_targetTheme = null;
        $this->_sourceFiles = array();
        $this->_targetFiles = array();
    }

    public function testCopyDatabaseCustomization()
    {
        $this->_sourceTheme->expects($this->once())->method('getFiles')->will($this->returnValue($this->_sourceFiles));
        $this->_targetTheme->expects($this->once())->method('getFiles')->will($this->returnValue($this->_targetFiles));

        foreach ($this->_targetFiles as $targetFile) {
            $targetFile->expects($this->once())->method('delete');
        }

        $newFileOne = $this->getMock('Mage_Core_Model_Theme_File', array('setData', 'save'), array(), '', false);
        $newFileTwo = $this->getMock('Mage_Core_Model_Theme_File', array('setData', 'save'), array(), '', false);
        $newFileOne->expects($this->at(0))->method('setData')->with(array(
            'theme_id'      => 123,
            'file_path'     => 'fixture_file_path_one',
            'file_type'     => 'fixture_file_type_one',
            'content'       => 'fixture_content_one',
            'sort_order'    => 10,
        ));
        $newFileOne->expects($this->at(1))->method('save');
        $newFileTwo->expects($this->at(0))->method('setData')->with(array(
            'theme_id'      => 123,
            'file_path'     => 'fixture_file_path_two',
            'file_type'     => 'fixture_file_type_two',
            'content'       => 'fixture_content_two',
            'sort_order'    => 20,
        ));
        $newFileTwo->expects($this->at(1))->method('save');
        $this->_objectManager
            ->expects($this->any())
            ->method('create')
            ->with('Mage_Core_Model_Theme_File')
            ->will($this->onConsecutiveCalls($newFileOne, $newFileTwo))
        ;

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);
    }

    public function testCopyFilesystemCustomization()
    {
        $this->_sourceTheme->expects($this->once())->method('getFiles')->will($this->returnValue(array()));
        $this->_targetTheme->expects($this->once())->method('getFiles')->will($this->returnValue(array()));

        $this->_sourceTheme
            ->expects($this->once())->method('getCustomizationPath')->will($this->returnValue('source/path'));

        $this->_targetTheme
            ->expects($this->once())->method('getCustomizationPath')->will($this->returnValue('target/path'));

        $this->_filesystem
            ->expects($this->at(0))->method('isDirectory')->with('source/path')->will($this->returnValue(true));

        $this->_filesystem
            ->expects($this->once())
            ->method('searchKeys')
            ->with('source/path', '*')
            ->will($this->returnValue(array(
                'source/path/file_one.jpg',
                'source/path/file_two.png',
            )))
        ;

        $expectedCopyEvents = array(
            array('source/path/file_one.jpg', 'target/path/file_one.jpg', 'source/path', 'target/path'),
            array('source/path/file_two.png', 'target/path/file_two.png', 'source/path', 'target/path'),
        );
        $actualCopyEvents = array();
        $recordCopyEvent = function () use (&$actualCopyEvents) {
            $actualCopyEvents[] = func_get_args();
        };
        $this->_filesystem->expects($this->any())->method('copy')->will($this->returnCallback($recordCopyEvent));

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);

        $this->assertEquals($expectedCopyEvents, $actualCopyEvents);
    }
}
