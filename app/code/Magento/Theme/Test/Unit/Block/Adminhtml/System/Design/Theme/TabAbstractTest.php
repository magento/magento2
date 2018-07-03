<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme;

class TabAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab::class,
            [
                $this->createMock(\Magento\Backend\Block\Template\Context::class),
                $this->createMock(\Magento\Framework\Registry::class),
                $this->createMock(\Magento\Framework\Data\FormFactory::class),
                $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
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
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['isVirtual', 'getId', '__wakeup']);
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
