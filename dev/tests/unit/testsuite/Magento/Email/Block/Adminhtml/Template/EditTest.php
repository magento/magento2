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
namespace Magento\Email\Block\Adminhtml\Template;

class EditTest extends \PHPUnit_Framework_TestCase
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
    protected $_fixtureConfigPath = array(
        array('scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/field1'),
        array('scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/group2/field1'),
        array('scope' => 'scope_11', 'scope_id' => 'scope_id_1', 'path' => 'section1/group1/group2/group3/field1')
    );

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false, false);
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false, false);
        $helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false, false);
        $menuConfigMock = $this->getMock('Magento\Backend\Model\Menu\Config', array(), array(), '', false, false);
        $menuMock = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Magento\Framework\Logger', [], [], '', false)]
        );
        $menuItemMock = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false, false);
        $urlBuilder = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false, false);
        $this->_configStructureMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_emailConfigMock = $this->getMock('Magento\Email\Model\Template\Config', array(), array(), '', false);

        $this->filesystemMock = $this->getMock(
            '\Magento\Framework\App\Filesystem',
            array('getFilesystem', '__wakeup', 'getPath', 'getDirectoryRead'),
            array(),
            '',
            false
        );

        $viewFilesystem = $this->getMock(
            '\Magento\Framework\View\Filesystem',
            array('getTemplateFileName'),
            array(),
            '',
            false
        );
        $viewFilesystem->expects(
            $this->any()
        )->method(
            'getTemplateFileName'
        )->will(
            $this->returnValue('var/www/magento\rootdir/app\custom/filename.phtml')
        );

        $params = array(
            'urlBuilder' => $urlBuilder,
            'registry' => $this->_registryMock,
            'layout' => $layoutMock,
            'menuConfig' => $menuConfigMock,
            'configStructure' => $this->_configStructureMock,
            'emailConfig' => $this->_emailConfigMock,
            'filesystem' => $this->filesystemMock,
            'viewFileSystem' => $viewFilesystem
        );
        $arguments = $objectManager->getConstructArguments('Magento\Email\Block\Adminhtml\Template\Edit', $params);

        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($menuMock));
        $menuMock->expects($this->any())->method('get')->will($this->returnValue($menuItemMock));
        $menuItemMock->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));

        $layoutMock->expects($this->any())->method('helper')->will($this->returnValue($helperMock));

        $this->_block = $objectManager->getObject('Magento\Email\Block\Adminhtml\Template\Edit', $arguments);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetUsedCurrentlyForPaths()
    {
        $sectionMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Section',
            array(),
            array(),
            '',
            false,
            false
        );
        $groupMock1 = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Group',
            array(),
            array(),
            '',
            false,
            false
        );
        $groupMock2 = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Group',
            array(),
            array(),
            '',
            false,
            false
        );
        $groupMock3 = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Group',
            array(),
            array(),
            '',
            false,
            false
        );
        $filedMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Field',
            array(),
            array(),
            '',
            false,
            false
        );
        $map = array(
            array(array('section1', 'group1'), $groupMock1),
            array(array('section1', 'group1', 'group2'), $groupMock2),
            array(array('section1', 'group1', 'group2', 'group3'), $groupMock3),
            array(array('section1', 'group1', 'field1'), $filedMock),
            array(array('section1', 'group1', 'group2', 'field1'), $filedMock),
            array(array('section1', 'group1', 'group2', 'group3', 'field1'), $filedMock)
        );
        $sectionMock->expects($this->any())->method('getLabel')->will($this->returnValue('Section_1_Label'));
        $groupMock1->expects($this->any())->method('getLabel')->will($this->returnValue('Group_1_Label'));
        $groupMock2->expects($this->any())->method('getLabel')->will($this->returnValue('Group_2_Label'));
        $groupMock3->expects($this->any())->method('getLabel')->will($this->returnValue('Group_3_Label'));
        $filedMock->expects($this->any())->method('getLabel')->will($this->returnValue('Field_1_Label'));

        $this->_configStructureMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->with(
            'section1'
        )->will(
            $this->returnValue($sectionMock)
        );

        $this->_configStructureMock->expects(
            $this->any()
        )->method(
            'getElementByPathParts'
        )->will(
            $this->returnValueMap($map)
        );

        $templateMock = $this->getMock('Magento\Email\Model\BackendTemplate', array(), array(), '', false, false);
        $templateMock->expects(
            $this->once()
        )->method(
            'getSystemConfigPathsWhereUsedCurrently'
        )->will(
            $this->returnValue($this->_fixtureConfigPath)
        );

        $this->_registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_email_template'
        )->will(
            $this->returnValue($templateMock)
        );

        $actual = $this->_block->getUsedCurrentlyForPaths(false);
        $expected = array(
            array(
                array('title' => __('Title')),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/'),
                array('title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'),
                array('title' => 'Group_1_Label'),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'))
            ),
            array(
                array('title' => __('Title')),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/'),
                array('title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'),
                array('title' => 'Group_1_Label'),
                array('title' => 'Group_2_Label'),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'))
            ),
            array(
                array('title' => __('Title')),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/'),
                array('title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit'),
                array('title' => 'Group_1_Label'),
                array('title' => 'Group_2_Label'),
                array('title' => 'Group_3_Label'),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'))
            )
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetDefaultTemplatesAsOptionsArray()
    {
        $dirValueMap = array(
            array(\Magento\Framework\App\Filesystem::ROOT_DIR, 'var/www/magento\rootdir/'),
            array(\Magento\Framework\App\Filesystem::APP_DIR, 'var/www/magento\rootdir\app/'),
            array(\Magento\Framework\App\Filesystem::THEMES_DIR, 'var\www/magento\rootdir\app/themes/')
        );

        $this->directoryMock = $this->getMock(
            '\Magento\Framework\Filesystem\Directory\Read',
            array(),
            array(),
            '',
            false
        );
        $this->directoryMock->expects($this->any())->method('isFile')->will($this->returnValue(false));
        $this->directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnValue(''));

        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($this->directoryMock)
        );
        $this->filesystemMock->expects($this->any())->method('getPath')->will($this->returnValueMap($dirValueMap));

        $this->_emailConfigMock
            ->expects($this->once())
            ->method('getAvailableTemplates')
            ->will($this->returnValue(array('template_b2', 'template_a', 'template_b1')));
        $this->_emailConfigMock
            ->expects($this->exactly(3))
            ->method('getTemplateModule')
            ->will($this->onConsecutiveCalls('Fixture_ModuleB', 'Fixture_ModuleA', 'Fixture_ModuleB'));
        $this->_emailConfigMock
            ->expects($this->exactly(3))
            ->method('getTemplateLabel')
            ->will($this->onConsecutiveCalls('Template B2', 'Template A', 'Template B1'));

        $this->assertEmpty($this->_block->getData('template_options'));
        $this->_block->setTemplate('my/custom\template.phtml');
        $this->_block->toHtml();
        $expectedResult = array(
            '' => array(array('value' => '', 'label' => '', 'group' => '')),
            'Fixture_ModuleA' => array(
                array('value' => 'template_a', 'label' => 'Template A', 'group' => 'Fixture_ModuleA')
            ),
            'Fixture_ModuleB' => array(
                array('value' => 'template_b1', 'label' => 'Template B1', 'group' => 'Fixture_ModuleB'),
                array('value' => 'template_b2', 'label' => 'Template B2', 'group' => 'Fixture_ModuleB')
            )
        );
        $this->assertEquals(
            $expectedResult,
            $this->_block->getData('template_options'),
            'Options are expected to be sorted by modules and by labels of email templates within modules'
        );
    }
}
