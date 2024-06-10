<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Config;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Url;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Email\Block\Adminhtml\Template\Edit;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\FileSystem as FilesystemView;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $_block;

    /**
     * @var MockObject
     */
    protected $_configStructureMock;

    /**
     * @var \Magento\Email\Model\Template\Config|MockObject
     */
    protected $_emailConfigMock;

    /**
     * @var array
     */
    protected $_fixtureConfigPath = [
        ['scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/field1'],
        ['scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/group2/field1'],
        ['scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/group2/group3/field1'],
    ];

    /**
     * @var MockObject
     */
    protected $filesystemMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->addMethods(['helper'])
            ->disableOriginalConstructor()
            ->getMock();
        $helperMock = $this->createMock(Data::class);
        $menuConfigMock = $this->createMock(Config::class);
        $menuMock = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->getMock();
        $menuItemMock = $this->createMock(Item::class);
        $urlBuilder = $this->createMock(Url::class);
        $this->_configStructureMock = $this->createMock(Structure::class);
        $this->_emailConfigMock = $this->createMock(\Magento\Email\Model\Template\Config::class);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->addMethods(['getFilesystem', 'getPath'])
            ->onlyMethods(['getDirectoryRead'])
            ->disableOriginalConstructor()
            ->getMock();

        $viewFilesystem = $this->getMockBuilder(FilesystemView::class)
            ->onlyMethods(['getTemplateFileName'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewFilesystem->expects(
            $this->any()
        )->method(
            'getTemplateFileName'
        )->willReturn(
            DirectoryList::ROOT . '/custom/filename.phtml'
        );

        $params = [
            'urlBuilder' => $urlBuilder,
            'layout' => $layoutMock,
            'menuConfig' => $menuConfigMock,
            'configStructure' => $this->_configStructureMock,
            'emailConfig' => $this->_emailConfigMock,
            'filesystem' => $this->filesystemMock,
            'viewFileSystem' => $viewFilesystem,
        ];
        $arguments = $objectManager->getConstructArguments(
            Edit::class,
            $params
        );

        $urlBuilder->expects($this->any())->method('getUrl')->willReturnArgument(0);
        $menuConfigMock->expects($this->any())->method('getMenu')->willReturn($menuMock);
        $menuMock->expects($this->any())->method('get')->willReturn($menuItemMock);
        $menuItemMock->expects($this->any())->method('getTitle')->willReturn('Title');

        $layoutMock->expects($this->any())->method('helper')->willReturn($helperMock);

        $this->_block = $objectManager->getObject(Edit::class, $arguments);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCurrentlyUsedForPaths()
    {
        $sectionMock = $this->createPartialMock(
            Section::class,
            ['getLabel']
        );
        $groupMock1 = $this->createPartialMock(
            Group::class,
            ['getLabel']
        );
        $groupMock2 = $this->createPartialMock(
            Group::class,
            ['getLabel']
        );
        $groupMock3 = $this->createPartialMock(
            Group::class,
            ['getLabel']
        );
        $filedMock = $this->createPartialMock(
            Field::class,
            ['getLabel']
        );
        $map = [
            [['section1', 'group1'], $groupMock1],
            [['section1', 'group1', 'group2'], $groupMock2],
            [['section1', 'group1', 'group2', 'group3'], $groupMock3],
            [['section1', 'group1', 'field1'], $filedMock],
            [['section1', 'group1', 'group2', 'field1'], $filedMock],
            [['section1', 'group1', 'group2', 'group3', 'field1'], $filedMock],
        ];
        $sectionMock->expects($this->any())->method('getLabel')->willReturn('Section_1_Label');
        $groupMock1->expects($this->any())->method('getLabel')->willReturn('Group_1_Label');
        $groupMock2->expects($this->any())->method('getLabel')->willReturn('Group_2_Label');
        $groupMock3->expects($this->any())->method('getLabel')->willReturn('Group_3_Label');
        $filedMock->expects($this->any())->method('getLabel')->willReturn('Field_1_Label');

        $this->_configStructureMock->expects($this->any())
            ->method('getElement')
            ->with('section1')
            ->willReturn($sectionMock);

        $this->_configStructureMock->expects($this->any())
            ->method('getElementByPathParts')
            ->willReturnMap($map);

        $templateMock = $this->createMock(BackendTemplate::class);
        $templateMock->expects($this->once())
            ->method('getSystemConfigPathsWhereCurrentlyUsed')
            ->willReturn($this->_fixtureConfigPath);

        $this->_block->setEmailTemplate($templateMock);

        $actual = $this->_block->getCurrentlyUsedForPaths(false);
        $expected = [
            [
                ['title' => __('Title')],
                ['title' => __('Title'), 'url' => 'adminhtml/system_config/'],
                ['title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'],
                ['title' => 'Group_1_Label'],
                ['title' => 'Field_1_Label', 'scope' => __('Default Config')],
            ],
            [
                ['title' => __('Title')],
                ['title' => __('Title'), 'url' => 'adminhtml/system_config/'],
                ['title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'],
                ['title' => 'Group_1_Label'],
                ['title' => 'Group_2_Label'],
                ['title' => 'Field_1_Label', 'scope' => __('Default Config')]
            ],
            [
                ['title' => __('Title')],
                ['title' => __('Title'), 'url' => 'adminhtml/system_config/'],
                ['title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'],
                ['title' => 'Group_1_Label'],
                ['title' => 'Group_2_Label'],
                ['title' => 'Group_3_Label'],
                ['title' => 'Field_1_Label', 'scope' => __('Default Config')]
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetDefaultTemplatesAsOptionsArray()
    {
        $directoryMock = $this->createMock(Read::class);

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($directoryMock);

        $this->_emailConfigMock
            ->expects($this->once())
            ->method('getAvailableTemplates')
            ->willReturn([
                [
                    'value' => 'template_b2',
                    'label' => 'Template B2',
                    'group' => 'Fixture_ModuleB',
                ],
                [
                    'value' => 'template_a',
                    'label' => 'Template A',
                    'group' => 'Fixture_ModuleA',
                ],
                [
                    'value' => 'template_b1',
                    'label' => 'Template B1',
                    'group' => 'Fixture_ModuleB',
                ],
            ]);

        $this->assertEmpty($this->_block->getData('template_options'));
        $this->_block->setTemplate('my/custom\template.phtml');
        $this->_block->toHtml();
        $expectedResult = [
            '' => [['value' => '', 'label' => '', 'group' => '']],
            'Fixture_ModuleA' => [
                ['value' => 'template_a', 'label' => 'Template A', 'group' => 'Fixture_ModuleA'],
            ],
            'Fixture_ModuleB' => [
                ['value' => 'template_b1', 'label' => 'Template B1', 'group' => 'Fixture_ModuleB'],
                ['value' => 'template_b2', 'label' => 'Template B2', 'group' => 'Fixture_ModuleB'],
            ],
        ];
        $this->assertEquals(
            $expectedResult,
            $this->_block->getData('template_options'),
            'Options are expected to be sorted by modules and by labels of email templates within modules'
        );
    }
}
