<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\Design\Theme\FileFactory;
use Magento\Theme\Model\CopyService;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\File;
use Magento\Widget\Model\Layout\Link;
use Magento\Widget\Model\Layout\Update;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class CopyServiceTest extends TestCase
{
    /**#@+
     * @var \Magento\Theme\Model\CopyService
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $fileFactory;

    /**
     * @var MockObject
     */
    protected $filesystem;

    /**
     * @var MockObject
     */
    protected $sourceTheme;

    /**
     * @var MockObject
     */
    protected $targetTheme;

    /**
     * @var MockObject
     */
    protected $link;

    /**
     * @var MockObject
     */
    protected $linkCollection;

    /**
     * @var MockObject
     */
    protected $update;

    /**
     * @var MockObject
     */
    protected $updateCollection;

    /**
     * @var MockObject
     */
    protected $updateFactory;

    /**
     * @var MockObject
     */
    protected $customizationPath;

    /**
     * @var MockObject[]
     */
    protected $targetFiles = [];

    /**
     * @var MockObject[]
     */
    protected $sourceFiles = [];

    /**
     * @var MockObject
     */
    protected $dirWriteMock;

    /**
     * @var array
     */
    private $updateFactoryReturn = [];

    /**
     * @var int
     */
    private $updateFactoryCalls = 0;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $sourceFileOne = $this->createPartialMock(File::class, ['__wakeup', 'delete']);
        $sourceFileOne->setData(
            [
                'file_path' => 'fixture_file_path_one',
                'file_type' => 'fixture_file_type_one',
                'content' => 'fixture_content_one',
                'sort_order' => 10
            ]
        );
        $sourceFileTwo = $this->createPartialMock(File::class, ['__wakeup', 'delete']);
        $sourceFileTwo->setData(
            [
                'file_path' => 'fixture_file_path_two',
                'file_type' => 'fixture_file_type_two',
                'content' => 'fixture_content_two',
                'sort_order' => 20
            ]
        );
        $this->sourceFiles = [$sourceFileOne, $sourceFileTwo];
        $this->sourceTheme = $this->createPartialMock(
            Theme::class,
            ['__wakeup', 'getCustomization']
        );

        $this->targetFiles = [
            $this->createPartialMock(File::class, ['__wakeup', 'delete']),
            $this->createPartialMock(File::class, ['__wakeup', 'delete'])
        ];
        $this->targetTheme = $this->createPartialMock(
            Theme::class,
            ['__wakeup', 'getCustomization']
        );
        $this->targetTheme->setId(123);

        $this->customizationPath = $this->createMock(Path::class);

        $this->fileFactory = $this->createPartialMock(
            FileFactory::class,
            ['create']
        );
        $this->filesystem =
            $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->dirWriteMock = $this->getMockBuilder(Write::class)
            ->addMethods(['copy'])
            ->onlyMethods(['isDirectory', 'search', 'delete', 'read', 'copyFile', 'isExist'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->willReturn(
            $this->dirWriteMock
        );

        /* Init \Magento\Widget\Model\ResourceModel\Layout\Update\Collection model  */
        $this->updateFactory = $this->createPartialMock(\Magento\Widget\Model\Layout\UpdateFactory::class, ['create']);
        $this->update = $this->createPartialMock(
            Update::class,
            ['__wakeup', 'getCollection']
        );
        $this->updateFactoryReturn = [$this->update];
        $classInstance = $this;
        $this->updateFactory
            ->method('create')
            ->will(
                $this->returnCallback(function () use ($classInstance) {
                    return $classInstance->updateFactoryReturn[$classInstance->updateFactoryCalls++];
                })
            );
        $this->updateCollection = $this->createPartialMock(
            Collection::class,
            ['addThemeFilter', 'delete', 'getIterator']
        );
        $this->update->expects(
            $this->any()
        )->method(
            'getCollection'
        )->willReturn(
            $this->updateCollection
        );

        /* Init Link an Link_Collection model */
        $this->link = $this->createPartialMock(Link::class, ['__wakeup', 'getCollection']);
        $this->linkCollection = $this->createPartialMock(
            \Magento\Widget\Model\ResourceModel\Layout\Link\Collection::class,
            ['addThemeFilter', 'getIterator', 'addFieldToFilter']
        );
        $this->link->expects($this->any())->method('getCollection')->willReturn($this->linkCollection);

        $eventManager = $this->createPartialMock(ManagerInterface::class, ['dispatch']);

        $this->object = new CopyService(
            $this->filesystem,
            $this->fileFactory,
            $this->link,
            $this->updateFactory,
            $eventManager,
            $this->customizationPath
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
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
     * @return void
     * cover \Magento\Theme\Model\CopyService::_copyLayoutCustomization
     */
    public function testCopyLayoutUpdates(): void
    {
        $customization = $this->createPartialMock(
            Customization::class,
            ['getFiles']
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $customization
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $customization
        );
        $this->updateCollection->expects($this->once())->method('delete');
        $this->linkCollection->expects($this->once())->method('addThemeFilter');
        $targetLinkOne = $this->getMockBuilder(Link::class)
            ->addMethods(['setThemeId', 'setLayoutUpdateId'])
            ->onlyMethods(['__wakeup', 'setId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $targetLinkOne->setData(['id' => 1, 'layout_update_id' => 1]);
        $targetLinkTwo = $this->getMockBuilder(Link::class)
            ->addMethods(['setThemeId', 'setLayoutUpdateId'])
            ->onlyMethods(['__wakeup', 'setId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $targetLinkTwo->setData(['id' => 2, 'layout_update_id' => 2]);
        $targetLinkOne
            ->method('setThemeId')->willReturnCallback(function ($arg1) {
                if ($arg1 == 123) {
                    return null;
                }
            });
        $targetLinkOne
            ->method('setLayoutUpdateId')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == 1) {
                    return null;
                }
            });
        $targetLinkOne
            ->method('setId')->willReturnCallback(function ($arg1) {
                if ($arg1 == 1) {
                    return null;
                }
            });
        $targetLinkOne
            ->method('save');
        $targetLinkTwo
            ->method('setThemeId')->willReturnCallback(function ($arg1) {
                if ($arg1 == 123) {
                    return null;
                }
            });
        $targetLinkTwo
            ->method('setLayoutUpdateId')->willReturnCallback(function ($arg1) {
                if ($arg1 == 2) {
                    return null;
                }
            });
        $targetLinkTwo
            ->method('setId')->willReturnCallback(function ($arg1) {
                if ($arg1 == null) {
                    return null;
                }
            });
        $targetLinkTwo
            ->method('save');
        $linkReturnValues = $this->onConsecutiveCalls(new \ArrayIterator([$targetLinkOne, $targetLinkTwo]));
        $this->linkCollection->expects($this->any())->method('getIterator')->will($linkReturnValues);
        $targetUpdateOne = $this->createPartialMock(
            Update::class,
            ['__wakeup', 'setId', 'load', 'save']
        );
        $targetUpdateOne->setData(['id' => 1]);
        $targetUpdateTwo = $this->createPartialMock(
            Update::class,
            ['__wakeup', 'setId', 'load', 'save']
        );
        $targetUpdateTwo->setData(['id' => 2]);
        $this->updateFactoryReturn = array_merge(
            $this->updateFactoryReturn,
            [
                $targetUpdateOne,
                $targetUpdateTwo
            ]
        );
        $this->object->copy($this->sourceTheme, $this->targetTheme);
    }

    /**
     * @return void
     * cover \Magento\Theme\Model\CopyService::_copyDatabaseCustomization
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCopyDatabaseCustomization(): void
    {
        $sourceCustom = $this->createPartialMock(
            Customization::class,
            ['getFiles']
        );
        $sourceCustom->expects(
            $this->atLeastOnce()
        )->method(
            'getFiles'
        )->willReturn(
            $this->sourceFiles
        );
        $this->sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $sourceCustom
        );
        $targetCustom = $this->createPartialMock(
            Customization::class,
            ['getFiles']
        );
        $targetCustom->expects(
            $this->atLeastOnce()
        )->method(
            'getFiles'
        )->willReturn(
            $this->targetFiles
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $targetCustom
        );

        $this->linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->willReturn(
            $this->linkCollection
        );
        $this->linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->willReturn(
            new \ArrayIterator([])
        );

        foreach ($this->targetFiles as $targetFile) {
            $targetFile->expects($this->once())->method('delete');
        }

        $newFileOne = $this->createPartialMock(File::class, ['__wakeup', 'setData', 'save']);
        $newFileTwo = $this->createPartialMock(File::class, ['__wakeup', 'setData', 'save']);

        $newFileOne
            ->method('setData')
            ->with(
                [
                    'theme_id' => 123,
                    'file_path' => 'fixture_file_path_one',
                    'file_type' => 'fixture_file_type_one',
                    'content' => 'fixture_content_one',
                    'sort_order' => 10
                ]
            );
        $newFileOne->method('save');

        $newFileTwo
            ->method('setData')
            ->with(
                [
                    'theme_id' => 123,
                    'file_path' => 'fixture_file_path_two',
                    'file_type' => 'fixture_file_type_two',
                    'content' => 'fixture_content_two',
                    'sort_order' => 20
                ]
            );
        $newFileTwo->method('save');

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
     * @return void
     * cover \Magento\Theme\Model\CopyService::_copyFilesystemCustomization
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCopyFilesystemCustomization(): void
    {
        $customization = $this->createPartialMock(
            Customization::class,
            ['getFiles']
        );
        $customization->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->sourceTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $customization
        );
        $this->targetTheme->expects(
            $this->once()
        )->method(
            'getCustomization'
        )->willReturn(
            $customization
        );

        $this->linkCollection->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->willReturn(
            $this->linkCollection
        );
        $this->linkCollection->expects(
            $this->any()
        )->method(
            'getIterator'
        )->willReturn(
            new \ArrayIterator([])
        );

        $this->customizationPath
            ->method('getCustomizationPath')
            ->willReturnOnConsecutiveCalls('source/path', 'target/path');

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->willReturnMap(
            [['source/path', true], ['source/path/subdir', true]]
        );

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'isExist'
        )->willReturnMap(
            [
                ['target/path', true]
            ]
        );

        $this->dirWriteMock->expects(
            $this->any()
        )->method(
            'read'
        )->willReturnMap(
            [
                ['target/path', ['target/path/subdir']],
                ['source/path', ['source/path/subdir']],
                ['source/path/subdir', ['source/path/subdir/file_one.jpg', 'source/path/subdir/file_two.png']]
            ]
        );

        $expectedCopyEvents = [
            ['source/path/subdir/file_one.jpg', 'target/path/subdir/file_one.jpg', null],
            ['source/path/subdir/file_two.png', 'target/path/subdir/file_two.png', null],
        ];
        $actualCopyEvents = [];
        $recordCopyEvent = function () use (&$actualCopyEvents) {
            $actualCopyEvents[] = func_get_args();
        };
        $this->dirWriteMock->expects($this->any())->method('copyFile')->willReturnCallback($recordCopyEvent);

        $this->object->copy($this->sourceTheme, $this->targetTheme);

        $this->assertEquals($expectedCopyEvents, $actualCopyEvents);
    }
}
