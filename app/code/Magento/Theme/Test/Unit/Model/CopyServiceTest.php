<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class CopyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * @var \Magento\Theme\Model\CopyService
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetTheme;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $link;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $update;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $targetFiles = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $sourceFiles = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirWriteMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $sourceFileOne = $this->getMock(
            'Magento\Theme\Model\Theme\File',
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
            'Magento\Theme\Model\Theme\File',
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
        $this->sourceFiles = [$sourceFileOne, $sourceFileTwo];
        $this->sourceTheme = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );

        $this->targetFiles = [
            $this->getMock('Magento\Theme\Model\Theme\File', ['__wakeup', 'delete'], [], '', false),
            $this->getMock('Magento\Theme\Model\Theme\File', ['__wakeup', 'delete'], [], '', false),
        ];
        $this->targetTheme = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );
        $this->targetTheme->setId(123);

        $this->customizationPath = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\Path',
            [],
            [],
            '',
            false
        );

        $this->fileFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FileFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->filesystem =
            $this->getMock('Magento\Framework\Filesystem', ['getDirectoryWrite'], [], '', false);
        $this->dirWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            ['isDirectory', 'search', 'copy', 'delete', 'read', 'copyFile', 'isExist'],
            [],
            '',
            false
        );
        $this->filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->will(
            $this->returnValue($this->dirWriteMock)
        );

        /* Init \Magento\Widget\Model\ResourceModel\Layout\Update\Collection model  */
        $this->updateFactory = $this->getMock(
            'Magento\Widget\Model\Layout\UpdateFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->update = $this->getMock(
            'Magento\Widget\Model\Layout\Update',
            ['__wakeup', 'getCollection'],
            [],
            '',
            false
        );
        $this->updateFactory->expects($this->at(0))->method('create')->will($this->returnValue($this->update));
        $this->updateCollection = $this->getMock(
            'Magento\Widget\Model\ResourceModel\Layout\Update\Collection',
            ['addThemeFilter', 'delete', 'getIterator'],
            [],
            '',
            false
        );
        $this->update->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue($this->updateCollection)
        );

        /* Init Link an Link_Collection model */
        $this->link = $this->getMock(
            'Magento\Widget\Model\Layout\Link',
            ['__wakeup', 'getCollection'],
            [],
            '',
            false
        );
        $this->linkCollection = $this->getMock(
            'Magento\Widget\Model\ResourceModel\Layout\Link\Collection',
            ['addThemeFilter', 'getIterator'],
            [],
            '',
            false
        );
        $this->link->expects($this->any())->method('getCollection')->will($this->returnValue($this->linkCollection));

        $eventManager = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            ['dispatch'],
            [],
            '',
            false
        );

        $this->object = new \Magento\Theme\Model\CopyService(
            $this->filesystem,
            $this->fileFactory,
            $this->link,
            $this->updateFactory,
            $eventManager,
            $this->customizationPath
        );
    }

    protected function tearDown()
    {
        $this->object = null;
        $this->filesystem = null;
        $this->fileFactory = null;
        $this->sourceTheme = null;
        $this->targetTheme = null;
        $this->link = null;
        $this->linkCollection = null;
        $this->updateCollection = null;
        $this->updateFactory = null;
        $this->sourceFiles = [];
        $this->targetFiles = [];
    }

    /**
     * cover \Magento\Theme\Model\CopyService::_copyLayoutCustomization
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
        $this->sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );

        $this->updateCollection->expects($this->once())->method('delete');
        $this->linkCollection->expects($this->once())->method('addThemeFilter');

        $targetLinkOne = $this->getMock(
            'Magento\Widget\Model\Layout\Link',
            ['__wakeup', 'setId', 'setThemeId', 'save', 'setLayoutUpdateId'],
            [],
            '',
            false
        );
        $targetLinkOne->setData(['id' => 1, 'layout_update_id' => 1]);
        $targetLinkTwo = $this->getMock(
            'Magento\Widget\Model\Layout\Link',
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
        $this->linkCollection->expects($this->any())->method('getIterator')->will($linkReturnValues);

        $targetUpdateOne = $this->getMock(
            'Magento\Widget\Model\Layout\Update',
            ['__wakeup', 'setId', 'load', 'save'],
            [],
            '',
            false
        );
        $targetUpdateOne->setData(['id' => 1]);
        $targetUpdateTwo = $this->getMock(
            'Magento\Widget\Model\Layout\Update',
            ['__wakeup', 'setId', 'load', 'save'],
            [],
            '',
            false
        );
        $targetUpdateTwo->setData(['id' => 2]);
        $updateReturnValues = $this->onConsecutiveCalls($this->update, $targetUpdateOne, $targetUpdateTwo);
        $this->updateFactory->expects($this->any())->method('create')->will($updateReturnValues);

        $this->object->copy($this->sourceTheme, $this->targetTheme);
    }

    /**
     * cover \Magento\Theme\Model\CopyService::_copyDatabaseCustomization
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
            $this->returnValue($this->sourceFiles)
        );
        $this->sourceTheme->expects(
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
            $this->returnValue($this->targetFiles)
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($targetCustom)
        );

        $this->linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValue($this->linkCollection)
        );
        $this->linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator([]))
        );

        foreach ($this->targetFiles as $targetFile) {
            $targetFile->expects($this->once())->method('delete');
        }

        $newFileOne = $this->getMock(
            'Magento\Theme\Model\Theme\File',
            ['__wakeup', 'setData', 'save'],
            [],
            '',
            false
        );
        $newFileTwo = $this->getMock(
            'Magento\Theme\Model\Theme\File',
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
        $this->fileFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            []
        )->will(
            $this->onConsecutiveCalls($newFileOne, $newFileTwo)
        );

        $this->object->copy($this->sourceTheme, $this->targetTheme);
    }

    /**
     * cover \Magento\Theme\Model\CopyService::_copyFilesystemCustomization
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
        $this->sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($customization)
        );

        $this->linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValue($this->linkCollection)
        );
        $this->linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator([]))
        );

        $this->customizationPath->expects(
            $this->at(0)
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue('source/path')
        );

        $this->customizationPath->expects(
            $this->at(1)
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue('target/path')
        );

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->will(
            $this->returnValueMap([['source/path', true], ['source/path/subdir', true]])
        );

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'isExist'
        )->will(
            $this->returnValueMap(
                [
                    ['target/path', true]
                ]
            )
        );

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'read'
        )->will(
            $this->returnValueMap(
                [
                    ['target/path', ['target/path/subdir']],
                    ['source/path', ['source/path/subdir']],
                    ['source/path/subdir', ['source/path/subdir/file_one.jpg', 'source/path/subdir/file_two.png']],
                ]
            )
        );

        $expectedCopyEvents = [
            ['source/path/subdir/file_one.jpg', 'target/path/subdir/file_one.jpg', null],
            ['source/path/subdir/file_two.png', 'target/path/subdir/file_two.png', null],
        ];
        $actualCopyEvents = [];
        $recordCopyEvent = function () use (&$actualCopyEvents) {
            $actualCopyEvents[] = func_get_args();
        };
        $this->dirWriteMock->expects($this->any())->method('copyFile')->will($this->returnCallback($recordCopyEvent));

        $this->object->copy($this->sourceTheme, $this->targetTheme);

        $this->assertEquals($expectedCopyEvents, $actualCopyEvents);
    }
}
