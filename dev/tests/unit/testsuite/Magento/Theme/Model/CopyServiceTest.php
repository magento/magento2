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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Model;

class CopyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sourceTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_targetTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_link;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_linkCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_update;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_updateCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_updateFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_targetFiles = array();

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_sourceFiles = array();

    protected $_dirWriteMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $sourceFileOne = $this->getMock(
            'Magento\Core\Model\Theme\File',
            array('__wakeup', 'delete'),
            array(),
            '',
            false
        );
        $sourceFileOne->setData(
            array(
                'file_path' => 'fixture_file_path_one',
                'file_type' => 'fixture_file_type_one',
                'content' => 'fixture_content_one',
                'sort_order' => 10
            )
        );
        $sourceFileTwo = $this->getMock(
            'Magento\Core\Model\Theme\File',
            array('__wakeup', 'delete'),
            array(),
            '',
            false
        );
        $sourceFileTwo->setData(
            array(
                'file_path' => 'fixture_file_path_two',
                'file_type' => 'fixture_file_type_two',
                'content' => 'fixture_content_two',
                'sort_order' => 20
            )
        );
        $this->_sourceFiles = array($sourceFileOne, $sourceFileTwo);
        $this->_sourceTheme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'getCustomization'),
            array(),
            '',
            false
        );

        $this->_targetFiles = array(
            $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'delete'), array(), '', false),
            $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'delete'), array(), '', false)
        );
        $this->_targetTheme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'getCustomization'),
            array(),
            '',
            false
        );
        $this->_targetTheme->setId(123);

        $this->_customizationPath = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\Path',
            array(),
            array(),
            '',
            false
        );

        $this->_fileFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FileFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_filesystem =
            $this->getMock('Magento\Framework\App\Filesystem', array('getDirectoryWrite'), array(), '', false);
        $this->_dirWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('isDirectory', 'search', 'copy', 'delete', 'read', 'copyFile', 'isExist'),
            array(),
            '',
            false
        );
        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::MEDIA_DIR
        )->will(
            $this->returnValue($this->_dirWriteMock)
        );

        /* Init \Magento\Core\Model\Resource\Layout\Collection model  */
        $this->_updateFactory = $this->getMock(
            'Magento\Core\Model\Layout\UpdateFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_update = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            array('__wakeup', 'getCollection'),
            array(),
            '',
            false
        );
        $this->_updateFactory->expects($this->at(0))->method('create')->will($this->returnValue($this->_update));
        $this->_updateCollection = $this->getMock(
            'Magento\Core\Model\Resource\Layout\Collection',
            array('addThemeFilter', 'delete', 'getIterator'),
            array(),
            '',
            false
        );
        $this->_update->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue($this->_updateCollection)
        );

        /* Init Link an Link_Collection model */
        $this->_link = $this->getMock(
            'Magento\Core\Model\Layout\Link',
            array('__wakeup', 'getCollection'),
            array(),
            '',
            false
        );
        $this->_linkCollection = $this->getMock(
            'Magento\Core\Model\Resource\Layout\Link\Collection',
            array('addThemeFilter', 'getIterator'),
            array(),
            '',
            false
        );
        $this->_link->expects($this->any())->method('getCollection')->will($this->returnValue($this->_linkCollection));

        $eventManager = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array('dispatch'),
            array(),
            '',
            false
        );

        $this->_object = new \Magento\Theme\Model\CopyService(
            $this->_filesystem,
            $this->_fileFactory,
            $this->_link,
            $this->_updateFactory,
            $eventManager,
            $this->_customizationPath
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_filesystem = null;
        $this->_fileFactory = null;
        $this->_sourceTheme = null;
        $this->_targetTheme = null;
        $this->_link = null;
        $this->_linkCollection = null;
        $this->_updateCollection = null;
        $this->_updateFactory = null;
        $this->_sourceFiles = array();
        $this->_targetFiles = array();
    }

    /**
     * @covers \Magento\Theme\Model\CopyService::_copyLayoutCustomization
     */
    public function testCopyLayoutUpdates()
    {
        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            array('getFiles'),
            array(),
            '',
            false
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->will($this->returnValue(array()));
        $this->_sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );
        $this->_targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );

        $this->_updateCollection->expects($this->once())->method('delete');
        $this->_linkCollection->expects($this->once())->method('addThemeFilter');

        $targetLinkOne = $this->getMock(
            'Magento\Core\Model\Layout\Link',
            array('__wakeup', 'setId', 'setThemeId', 'save', 'setLayoutUpdateId'),
            array(),
            '',
            false
        );
        $targetLinkOne->setData(array('id' => 1, 'layout_update_id' => 1));
        $targetLinkTwo = $this->getMock(
            'Magento\Core\Model\Layout\Link',
            array('__wakeup', 'setId', 'setThemeId', 'save', 'setLayoutUpdateId'),
            array(),
            '',
            false
        );
        $targetLinkTwo->setData(array('id' => 2, 'layout_update_id' => 2));

        $targetLinkOne->expects($this->at(0))->method('setThemeId')->with(123);
        $targetLinkOne->expects($this->at(1))->method('setLayoutUpdateId')->with(1);
        $targetLinkOne->expects($this->at(2))->method('setId')->with(null);
        $targetLinkOne->expects($this->at(3))->method('save');

        $targetLinkTwo->expects($this->at(0))->method('setThemeId')->with(123);
        $targetLinkTwo->expects($this->at(1))->method('setLayoutUpdateId')->with(2);
        $targetLinkTwo->expects($this->at(2))->method('setId')->with(null);
        $targetLinkTwo->expects($this->at(3))->method('save');

        $linkReturnValues = $this->onConsecutiveCalls(new \ArrayIterator(array($targetLinkOne, $targetLinkTwo)));
        $this->_linkCollection->expects($this->any())->method('getIterator')->will($linkReturnValues);

        $targetUpdateOne = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            array('__wakeup', 'setId', 'load', 'save'),
            array(),
            '',
            false
        );
        $targetUpdateOne->setData(array('id' => 1));
        $targetUpdateTwo = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            array('__wakeup', 'setId', 'load', 'save'),
            array(),
            '',
            false
        );
        $targetUpdateTwo->setData(array('id' => 2));
        $updateReturnValues = $this->onConsecutiveCalls($this->_update, $targetUpdateOne, $targetUpdateTwo);
        $this->_updateFactory->expects($this->any())->method('create')->will($updateReturnValues);

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);
    }

    /**
     * @covers \Magento\Theme\Model\CopyService::_copyDatabaseCustomization
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCopyDatabaseCustomization()
    {
        $sourceCustom = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            array('getFiles'),
            array(),
            '',
            false
        );
        $sourceCustom->expects(
            $this->atLeastOnce()
        )->method(
            'getFiles'
        )->will(
            $this->returnValue($this->_sourceFiles)
        );
        $this->_sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($sourceCustom)
        );
        $targetCustom = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            array('getFiles'),
            array(),
            '',
            false
        );
        $targetCustom->expects(
            $this->atLeastOnce()
        )->method(
            'getFiles'
        )->will(
            $this->returnValue($this->_targetFiles)
        );
        $this->_targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($targetCustom)
        );

        $this->_linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValue($this->_linkCollection)
        );
        $this->_linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator(array()))
        );

        foreach ($this->_targetFiles as $targetFile) {
            $targetFile->expects($this->once())->method('delete');
        }

        $newFileOne = $this->getMock(
            'Magento\Core\Model\Theme\File',
            array('__wakeup', 'setData', 'save'),
            array(),
            '',
            false
        );
        $newFileTwo = $this->getMock(
            'Magento\Core\Model\Theme\File',
            array('__wakeup', 'setData', 'save'),
            array(),
            '',
            false
        );
        $newFileOne->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            array(
                'theme_id' => 123,
                'file_path' => 'fixture_file_path_one',
                'file_type' => 'fixture_file_type_one',
                'content' => 'fixture_content_one',
                'sort_order' => 10
            )
        );
        $newFileOne->expects($this->at(1))->method('save');
        $newFileTwo->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            array(
                'theme_id' => 123,
                'file_path' => 'fixture_file_path_two',
                'file_type' => 'fixture_file_type_two',
                'content' => 'fixture_content_two',
                'sort_order' => 20
            )
        );
        $newFileTwo->expects($this->at(1))->method('save');
        $this->_fileFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            array()
        )->will(
            $this->onConsecutiveCalls($newFileOne, $newFileTwo)
        );

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);
    }

    /**
     * @covers \Magento\Theme\Model\CopyService::_copyFilesystemCustomization
     */
    public function testCopyFilesystemCustomization()
    {
        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            array('getFiles'),
            array(),
            '',
            false
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->will($this->returnValue(array()));
        $this->_sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );
        $this->_targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );

        $this->_linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValue($this->_linkCollection)
        );
        $this->_linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator(array()))
        );

        $this->_customizationPath->expects(
            $this->at(0)
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue('source/path')
        );

        $this->_customizationPath->expects(
            $this->at(1)
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue('target/path')
        );

        $this->_dirWriteMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->will(
            $this->returnValueMap(array(array('source/path', true)))
        );

        $this->_dirWriteMock->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValueMap(
                array(
                    array('target/path', array()),
                    array('source/path', array('source/path/file_one.jpg', 'source/path/file_two.png'))
                )
            )
        );

        $expectedCopyEvents = array(
            array('source/path/file_one.jpg', 'target/path/file_one.jpg', null),
            array('source/path/file_two.png', 'target/path/file_two.png', null)
        );
        $actualCopyEvents = array();
        $recordCopyEvent = function () use (&$actualCopyEvents) {
            $actualCopyEvents[] = func_get_args();
        };
        $this->_dirWriteMock->expects($this->any())->method('copyFile')->will($this->returnCallback($recordCopyEvent));

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);

        $this->assertEquals($expectedCopyEvents, $actualCopyEvents);
    }
}
