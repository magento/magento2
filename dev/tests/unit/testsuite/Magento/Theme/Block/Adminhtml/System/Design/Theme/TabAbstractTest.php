<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme;

class TabAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab',
            [
                $this->getMock('Magento\Backend\Block\Template\Context', [], [], '', false),
                $this->getMock('Magento\Framework\Registry', [], [], '', false),
                $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false),
                $this->getMock('Magento\Framework\ObjectManagerInterface'),
            ],
            '',
            true,
            false,
            true,
            ['_getCurrentTheme', 'getTabLabel']
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetTabTitle()
    {
        $label = 'test label';
        $this->_model->expects($this->once())->method('getTabLabel')->will($this->returnValue($label));
        $this->assertEquals($label, $this->_model->getTabTitle());
    }

    /**
     * @dataProvider canShowTabDataProvider
     * @param bool $isVirtual
     * @param int $themeId
     * @param bool $result
     */
    public function testCanShowTab($isVirtual, $themeId, $result)
    {
        $themeMock = $this->getMock(
            'Magento\Core\Model\Theme',
            ['isVirtual', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $themeMock->expects($this->any())->method('isVirtual')->will($this->returnValue($isVirtual));

        $themeMock->expects($this->any())->method('getId')->will($this->returnValue($themeId));

        $this->_model->expects($this->any())->method('_getCurrentTheme')->will($this->returnValue($themeMock));

        if ($result === true) {
            $this->assertTrue($this->_model->canShowTab());
        } else {
            $this->assertFalse($this->_model->canShowTab());
        }
    }

    /**
     * @return array
     */
    public function canShowTabDataProvider()
    {
        return [[true, 1, true], [true, 0, false], [false, 1, false]];
    }

    public function testIsHidden()
    {
        $this->assertFalse($this->_model->isHidden());
    }
}
