<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

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
    protected $_targetFiles = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_sourceFiles = [];

    protected $_dirWriteMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $sourceFileOne = $this->getMock(
            'Magento\Core\Model\Theme\File',
            ['__wakeup', 'delete'],
            [],
            '',
            false
        );
        $sourceFileOne->setData(
            [
                'file_path' => 'fixture_file_path_one',
                'file_type' => 'fixture_file_type_one',
                'content' => 'fixture_content_one',
                'sort_order' => 10,
            ]
        );
        $sourceFileTwo = $this->getMock(
            'Magento\Core\Model\Theme\File',
            ['__wakeup', 'delete'],
            [],
            '',
            false
        );
        $sourceFileTwo->setData(
            [
                'file_path' => 'fixture_file_path_two',
                'file_type' => 'fixture_file_type_two',
                'content' => 'fixture_content_two',
                'sort_order' => 20,
            ]
        );
        $this->_sourceFiles = [$sourceFileOne, $sourceFileTwo];
        $this->_sourceTheme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );

        $this->_targetFiles = [
            $this->getMock('Magento\Core\Model\Theme\File', ['__wakeup', 'delete'], [], '', false),
            $this->getMock('Magento\Core\Model\Theme\File', ['__wakeup', 'delete'], [], '', false),
        ];
        $this->_targetTheme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );
        $this->_targetTheme->setId(123);

        $this->_customizationPath = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\Path',
            [],
            [],
            '',
            false
        );

        $this->_fileFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FileFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_filesystem =
            $this->getMock('Magento\Framework\Filesystem', ['getDirectoryWrite'], [], '', false);
        $this->_dirWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            ['isDirectory', 'search', 'copy', 'delete', 'read', 'copyFile', 'isExist'],
            [],
            '',
            false
        );
        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->will(
            $this->returnValue($this->_dirWriteMock)
        );

        /* Init \Magento\Core\Model\Resource\Layout\Collection model  */
        $this->_updateFactory = $this->getMock(
            'Magento\Core\Model\Layout\UpdateFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_update = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            ['__wakeup', 'getCollection'],
            [],
            '',
            false
        );
        $this->_updateFactory->expects($this->at(0))->method('create')->will($this->returnValue($this->_update));
        $this->_updateCollection = $this->getMock(
            'Magento\Core\Model\Resource\Layout\Collection',
            ['addThemeFilter', 'delete', 'getIterator'],
            [],
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
            ['__wakeup', 'getCollection'],
            [],
            '',
            false
        );
        $this->_linkCollection = $this->getMock(
            'Magento\Core\Model\Resource\Layout\Link\Collection',
            ['addThemeFilter', 'getIterator'],
            [],
            '',
            false
        );
        $this->_link->expects($this->any())->method('getCollection')->will($this->returnValue($this->_linkCollection));

        $eventManager = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            ['dispatch'],
            [],
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
        $this->_sourceFiles = [];
        $this->_targetFiles = [];
    }

    /**
     * @covers \Magento\Theme\Model\CopyService::_copyLayoutCustomization
     */
    public function testCopyLayoutUpdates()
    {
        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            ['getFiles'],
            [],
            '',
            false
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->will($this->returnValue([]));
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
            ['__wakeup', 'setId', 'setThemeId', 'save', 'setLayoutUpdateId'],
            [],
            '',
            false
        );
        $targetLinkOne->setData(['id' => 1, 'layout_update_id' => 1]);
        $targetLinkTwo = $this->getMock(
            'Magento\Core\Model\Layout\Link',
            ['__wakeup', 'setId', 'setThemeId', 'save', 'setLayoutUpdateId'],
            [],
            '',
            false
        );
        $targetLinkTwo->setData(['id' => 2, 'layout_update_id' => 2]);

        $targetLinkOne->expects($this->at(0))->method('setThemeId')->with(123);
        $targetLinkOne->expects($this->at(1))->method('setLayoutUpdateId')->with(1);
        $targetLinkOne->expects($this->at(2))->method('setId')->with(null);
        $targetLinkOne->expects($this->at(3))->method('save');

        $targetLinkTwo->expects($this->at(0))->method('setThemeId')->with(123);
        $targetLinkTwo->expects($this->at(1))->method('setLayoutUpdateId')->with(2);
        $targetLinkTwo->expects($this->at(2))->method('setId')->with(null);
        $targetLinkTwo->expects($this->at(3))->method('save');

        $linkReturnValues = $this->onConsecutiveCalls(new \ArrayIterator([$targetLinkOne, $targetLinkTwo]));
        $this->_linkCollection->expects($this->any())->method('getIterator')->will($linkReturnValues);

        $targetUpdateOne = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            ['__wakeup', 'setId', 'load', 'save'],
            [],
            '',
            false
        );
        $targetUpdateOne->setData(['id' => 1]);
        $targetUpdateTwo = $this->getMock(
            'Magento\Core\Model\Layout\Update',
            ['__wakeup', 'setId', 'load', 'save'],
            [],
            '',
            false
        );
        $targetUpdateTwo->setData(['id' => 2]);
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
            ['getFiles'],
            [],
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
            ['getFiles'],
            [],
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
            $this->returnValue(new \ArrayIterator([]))
        );

        foreach ($this->_targetFiles as $targetFile) {
            $targetFile->expects($this->once())->method('delete');
        }

        $newFileOne = $this->getMock(
            'Magento\Core\Model\Theme\File',
            ['__wakeup', 'setData', 'save'],
            [],
            '',
            false
        );
        $newFileTwo = $this->getMock(
            'Magento\Core\Model\Theme\File',
            ['__wakeup', 'setData', 'save'],
            [],
            '',
            false
        );
        $newFileOne->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            [
                'theme_id' => 123,
                'file_path' => 'fixture_file_path_one',
                'file_type' => 'fixture_file_type_one',
                'content' => 'fixture_content_one',
                'sort_order' => 10,
            ]
        );
        $newFileOne->expects($this->at(1))->method('save');
        $newFileTwo->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            [
                'theme_id' => 123,
                'file_path' => 'fixture_file_path_two',
                'file_type' => 'fixture_file_type_two',
                'content' => 'fixture_content_two',
                'sort_order' => 20,
            ]
        );
        $newFileTwo->expects($this->at(1))->method('save');
        $this->_fileFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            []
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
            ['getFiles'],
            [],
            '',
            false
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->will($this->returnValue([]));
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
            $this->returnValue(new \ArrayIterator([]))
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
            $this->returnValueMap([['source/path', true]])
        );

        $this->_dirWriteMock->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValueMap(
                [
                    ['target/path', []],
                    ['source/path', ['source/path/file_one.jpg', 'source/path/file_two.png']],
                ]
            )
        );

        $expectedCopyEvents = [
            ['source/path/file_one.jpg', 'target/path/file_one.jpg', null],
            ['source/path/file_two.png', 'target/path/file_two.png', null],
        ];
        $actualCopyEvents = [];
        $recordCopyEvent = function () use (&$actualCopyEvents) {
            $actualCopyEvents[] = func_get_args();
        };
        $this->_dirWriteMock->expects($this->any())->method('copyFile')->will($this->returnCallback($recordCopyEvent));

        $this->_object->copy($this->_sourceTheme, $this->_targetTheme);

        $this->assertEquals($expectedCopyEvents, $actualCopyEvents);
    }
}
