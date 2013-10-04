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
 * @package     Magento_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Adminhtml\Block\System\Email\Template;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Adminhtml\Block\System\Email\Template\Edit
     */
    protected $_block;

    /**
     * @var \Magento\Core\Model\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configStructureMock;

    /**
     * @var \Magento\Core\Model\Email\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_emailConfigMock;

    /**
     * @var array
     */
    protected $_fixtureConfigPath = array(
        array(
            'scope'     => 'scope_11',
            'scope_id'  => 'scope_id_1',
            'path'      => 'section1/group1/field1',
        ),
        array(
            'scope'     => 'scope_11',
            'scope_id'  => 'scope_id_1',
            'path'      => 'section1/group1/group2/field1',
        ),
        array(
            'scope'     => 'scope_11',
            'scope_id'  => 'scope_id_1',
            'path'      => 'section1/group1/group2/group3/field1',
        ),
    );

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_registryMock = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false, false);
        $layoutMock = $this->getMock('Magento\Core\Model\Layout', array(), array(), '', false, false);
        $helperMock = $this->getMock('Magento\Adminhtml\Helper\Data', array(), array(), '', false, false);
        $menuConfigMock = $this->getMock('Magento\Backend\Model\Menu\Config', array(), array(), '', false, false);
        $menuMock = $this->getMock('Magento\Backend\Model\Menu', array(), array(), '', false, false);
        $menuItemMock = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false, false);
        $urlBuilder = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false, false);
        $this->_configStructureMock = $this->getMock('Magento\Backend\Model\Config\Structure',
            array(), array(), '', false, false
        );
        $this->_emailConfigMock = $this->getMock(
            'Magento\Core\Model\Email\Template\Config', array(), array(), '', false
        );

        $params = array(
            'urlBuilder' => $urlBuilder,
            'registry' => $this->_registryMock,
            'layout' => $layoutMock,
            'menuConfig' => $menuConfigMock,
            'configStructure' => $this->_configStructureMock,
            'emailConfig' => $this->_emailConfigMock,
        );
        $arguments = $objectManager->getConstructArguments(
            'Magento\Adminhtml\Block\System\Email\Template\Edit',
            $params
        );

        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($menuMock));
        $menuMock->expects($this->any())->method('get')->will($this->returnValue($menuItemMock));
        $menuItemMock->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));

        $layoutMock->expects($this->any())->method('helper')->will($this->returnValue($helperMock));

        $this->_block = $objectManager->getObject('Magento\Adminhtml\Block\System\Email\Template\Edit', $arguments);
    }

    public function testGetUsedCurrentlyForPaths()
    {
        $sectionMock = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Section',
            array(), array(), '', false, false
        );
        $groupMock1 = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Group',
            array(), array(), '', false, false
        );
        $groupMock2 = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Group',
            array(), array(), '', false, false
        );
        $groupMock3 = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Group',
            array(), array(), '', false, false
        );
        $filedMock = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Field',
            array(), array(), '', false, false
        );
        $map = array(
            array(array('section1', 'group1'), $groupMock1),
            array(array('section1', 'group1', 'group2'), $groupMock2),
            array(array('section1', 'group1', 'group2', 'group3'), $groupMock3),
            array(array('section1', 'group1', 'field1'), $filedMock),
            array(array('section1', 'group1', 'group2', 'field1'), $filedMock),
            array(array('section1', 'group1', 'group2', 'group3', 'field1'), $filedMock),
        );
        $sectionMock->expects($this->any())->method('getLabel')->will($this->returnValue('Section_1_Label'));
        $groupMock1->expects($this->any())->method('getLabel')->will($this->returnValue('Group_1_Label'));
        $groupMock2->expects($this->any())->method('getLabel')->will($this->returnValue('Group_2_Label'));
        $groupMock3->expects($this->any())->method('getLabel')->will($this->returnValue('Group_3_Label'));
        $filedMock->expects($this->any())->method('getLabel')->will($this->returnValue('Field_1_Label'));

        $this->_configStructureMock->expects($this->any())
            ->method('getElement')->with('section1')->will($this->returnValue($sectionMock));

        $this->_configStructureMock->expects($this->any())
            ->method('getElementByPathParts')->will($this->returnValueMap($map));

        $templateMock = $this->getMock('Magento\Adminhtml\Model\Email\Template', array(), array(), '', false, false);
        $templateMock->expects($this->once())
            ->method('getSystemConfigPathsWhereUsedCurrently')
            ->will($this->returnValue($this->_fixtureConfigPath));

        $this->_registryMock->expects($this->once())->method('registry')
            ->with('current_email_template')->will($this->returnValue($templateMock));

        $actual = $this->_block->getUsedCurrentlyForPaths(false);
        $expected = array(
            array(
                array('title' => __('Title'),),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/',),
                array('title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit',),
                array('title' => 'Group_1_Label',),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'),),
            ),
            array(
                array('title' => __('Title'),),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/',),
                array('title' => 'Section_1_Label', 'url'   => 'adminhtml/system_config/edit',),
                array('title' => 'Group_1_Label',),
                array('title' => 'Group_2_Label',),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'),),
            ),
            array(
                array('title' => __('Title'),),
                array('title' => __('Title'), 'url' => 'adminhtml/system_config/',),
                array('title' => 'Section_1_Label', 'url' => 'adminhtml/system_config/edit',),
                array('title' => 'Group_1_Label',),
                array('title' => 'Group_2_Label',),
                array('title' => 'Group_3_Label',),
                array('title' => 'Field_1_Label', 'scope' => __('GLOBAL'),),
            )
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetDefaultTemplatesAsOptionsArray()
    {
        $this->_emailConfigMock
            ->expects($this->once())
            ->method('getAvailableTemplates')
            ->will($this->returnValue(array('template_b2', 'template_a', 'template_b1')))
        ;
        $this->_emailConfigMock
            ->expects($this->exactly(3))
            ->method('getTemplateModule')
            ->will($this->onConsecutiveCalls('Fixture_ModuleB', 'Fixture_ModuleA', 'Fixture_ModuleB'))
        ;
        $this->_emailConfigMock
            ->expects($this->exactly(3))
            ->method('getTemplateLabel')
            ->will($this->onConsecutiveCalls('Template B2', 'Template A', 'Template B1'))
        ;
        $this->assertEmpty($this->_block->getData('template_options'));
        $this->_block->toHtml();
        $expectedResult = array (
            '' => array(
                array(
                    'value' => '',
                    'label' => '',
                    'group' => '',
                ),
            ),
            'Fixture_ModuleA' => array(
                array(
                    'value' => 'template_a',
                    'label' => 'Template A',
                    'group' => 'Fixture_ModuleA',
                ),
            ),
            'Fixture_ModuleB' => array(
                array(
                    'value' => 'template_b1',
                    'label' => 'Template B1',
                    'group' => 'Fixture_ModuleB',
                ),
                array(
                    'value' => 'template_b2',
                    'label' => 'Template B2',
                    'group' => 'Fixture_ModuleB',
                ),
            ),
        );
        $this->assertEquals(
            $expectedResult,
            $this->_block->getData('template_options'),
            'Options are expected to be sorted by modules and by labels of email templates within modules'
        );
    }
}
