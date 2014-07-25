<?php
/**
 *
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
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

class SaveTest extends \Magento\Theme\Controller\Adminhtml\System\Design\ThemeTest
{
    /**
     * @var string
     */
    protected $name = 'Save';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveAction()
    {
        $themeData = array('theme_id' => 123);
        $customCssContent = 'custom css content';
        $jsRemovedFiles = array(3, 4);
        $jsOrder = array(1 => '1', 2 => 'test');

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('back', false)
            ->will($this->returnValue(true));

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('theme')
            ->will($this->returnValue($themeData));

        $this->_request->expects($this->at(2))
            ->method('getParam')
            ->with('custom_css_content')
            ->will($this->returnValue($customCssContent));

        $this->_request->expects($this->at(3))
            ->method('getParam')
            ->with('js_removed_files')
            ->will($this->returnValue($jsRemovedFiles));

        $this->_request->expects($this->at(4))
            ->method('getParam')
            ->with('js_order')
            ->will($this->returnValue($jsOrder));

        $this->_request->expects($this->once(5))->method('getPost')->will($this->returnValue(true));

        $themeMock = $this->getMock(
            'Magento\Core\Model\Theme',
            array('save', 'load', 'setCustomization', 'getThemeImage', '__wakeup'),
            array(),
            '',
            false
        );

        $themeImage = $this->getMock('Magento\Core\Model\Theme\Image', array(), array(), '', false);
        $themeMock->expects($this->any())->method('getThemeImage')->will($this->returnValue($themeImage));

        $themeFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FlyweightFactory',
            array('create'),
            array(),
            '',
            false
        );
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeMock));

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with('Magento\Framework\View\Design\Theme\FlyweightFactory')
            ->will($this->returnValue($themeFactory));

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with('Magento\Theme\Model\Theme\Customization\File\CustomCss')
            ->will($this->returnValue(null));

        $this->_objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with('Magento\Theme\Model\Theme\SingleFile')
            ->will($this->returnValue(null));

        $this->_model->execute();
    }
}
