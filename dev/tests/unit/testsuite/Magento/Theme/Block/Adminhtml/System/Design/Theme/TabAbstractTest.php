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
            array(
                $this->getMock('Magento\Backend\Block\Template\Context', array(), array(), '', false),
                $this->getMock('Magento\Framework\Registry', array(), array(), '', false),
                $this->getMock('Magento\Framework\Data\FormFactory', array(), array(), '', false),
                $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false)
            ),
            '',
            true,
            false,
            true,
            array('_getCurrentTheme', 'getTabLabel')
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
            array('isVirtual', 'getId', '__wakeup'),
            array(),
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
        return array(array(true, 1, true), array(true, 0, false), array(false, 1, false));
    }

    public function testIsHidden()
    {
        $this->assertFalse($this->_model->isHidden());
    }
}
