<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Edit
     */
    protected $_block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configStructureMock;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['helper']);
        $helperMock = $this->createMock(\Magento\Backend\Helper\Data::class);
        $menuConfigMock = $this->createMock(\Magento\Backend\Model\Menu\Config::class);
        $menuMock = $this->getMockBuilder(\Magento\Backend\Model\Menu::class)
            ->setConstructorArgs([$this->createMock(\Psr\Log\LoggerInterface::class)])
            ->getMock();
        $menuItemMock = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $urlBuilder = $this->createMock(\Magento\Backend\Model\Url::class);
        $this->_configStructureMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);
        $this->_emailConfigMock = $this->createMock(\Magento\Email\Model\Template\Config::class);

        $this->filesystemMock = $this->createPartialMock(
            \Magento\Framework\Filesystem::class,
            ['getFilesystem', '__wakeup', 'getPath', 'getDirectoryRead']
        );

        $viewFilesystem = $this->getMockBuilder('Magento\Framework\View\Filesystem')
            ->setMethods(['getTemplateFileName'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewFilesystem->expects(
            $this->any()
        )->method(
            'getTemplateFileName'
        )->will(
            $this->returnValue(DirectoryList::ROOT . '/custom/filename.phtml')
        );

        $params = [
            'urlBuilder' => $urlBuilder,
            'registry' => $this->_registryMock,
            'layout' => $layoutMock,
            'menuConfig' => $menuConfigMock,
            'configStructure' => $this->_configStructureMock,
            'emailConfig' => $this->_emailConfigMock,
            'filesystem' => $this->filesystemMock,
            'viewFileSystem' => $viewFilesystem,
        ];
        $arguments = $objectManager->getConstructArguments(
            \Magento\Email\Block\Adminhtml\Template\Edit::class,
            $params
        );

        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($menuMock));
        $menuMock->expects($this->any())->method('get')->will($this->returnValue($menuItemMock));
        $menuItemMock->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));

        $layoutMock->expects($this->any())->method('helper')->will($this->returnValue($helperMock));

        $this->_block = $objectManager->getObject(\Magento\Email\Block\Adminhtml\Template\Edit::class, $arguments);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCurrentlyUsedForPaths()
    {
        $sectionMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Element\Section::class,
            ['getLabel']
        );
        $groupMock1 = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Element\Group::class,
            ['getLabel']
        );
        $groupMock2 = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Element\Group::class,
            ['getLabel']
        );
        $groupMock3 = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Element\Group::class,
            ['getLabel']
        );
        $filedMock = $this->createPartialMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
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
        $sectionMock->expects($this->any())->method('getLabel')->will($this->returnValue('Section_1_Label'));
        $groupMock1->expects($this->any())->method('getLabel')->will($this->returnValue('Group_1_Label'));
        $groupMock2->expects($this->any())->method('getLabel')->will($this->returnValue('Group_2_Label'));
        $groupMock3->expects($this->any())->method('getLabel')->will($this->returnValue('Group_3_Label'));
        $filedMock->expects($this->any())->method('getLabel')->will($this->returnValue('Field_1_Label'));

        $this->_configStructureMock->expects($this->any())
            ->method('getElement')
            ->with('section1')
            ->will($this->returnValue($sectionMock));

        $this->_configStructureMock->expects($this->any())
            ->method('getElementByPathParts')
            ->will($this->returnValueMap($map));

        $templateMock = $this->createMock(\Magento\Email\Model\BackendTemplate::class);
        $templateMock->expects($this->once())
            ->method('getSystemConfigPathsWhereCurrentlyUsed')
            ->will($this->returnValue($this->_fixtureConfigPath));

        $this->_registryMock->expects($this->once())
            ->method('registry')
            ->with('current_email_template')
            ->will($this->returnValue($templateMock));

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
        $directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValue($directoryMock));

        $this->_emailConfigMock
            ->expects($this->once())
            ->method('getAvailableTemplates')
            ->will($this->returnValue(
                [
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
                ]
            ));

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
