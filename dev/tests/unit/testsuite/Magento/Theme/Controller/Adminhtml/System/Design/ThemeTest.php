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
 * @package     Magento_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test backend controller for the theme
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Controller\Adminhtml\System\Design\Theme
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager', array(), array(), '', false);

        $this->_request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'request' => $this->_request,
            'objectManager' => $this->_objectManagerMock,

        );
        $context = $helper->getObject('Magento\Backend\Controller\Context', $arguments);
        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        $this->_model = $this->getMock('Magento\Theme\Controller\Adminhtml\System\Design\Theme',
            array('_forward', '_title', 'loadLayout', 'renderLayout', '_redirect'),
            array($context, $coreRegistry, null)
        );
        $this->_model->expects($this->any())->method('_title')->will($this->returnValue($this->_model));
        $this->_model->expects($this->any())->method('loadLayout');
        $this->_model->expects($this->any())->method('renderLayout');
    }

    /**
     * @covers \Magento\Theme\Controller\Adminhtml\System\Design\Theme::saveAction
     */
    public function testSaveAction()
    {
        $themeData = array('theme_id' => 123);
        $customCssContent = 'custom css content';
        $jsRemovedFiles = array(3, 4);
        $jsOrder = array(1 => '1', 2 => 'test');

        $this->_request->expects($this->at(0))->method('getParam')->with('back', false)
            ->will($this->returnValue(true));

        $this->_request->expects($this->at(1))->method('getParam')->with('theme')
            ->will($this->returnValue($themeData));
        $this->_request->expects($this->at(2))->method('getParam')->with('custom_css_content')
            ->will($this->returnValue($customCssContent));
        $this->_request->expects($this->at(3))->method('getParam')->with('js_removed_files')
            ->will($this->returnValue($jsRemovedFiles));
        $this->_request->expects($this->at(4))->method('getParam')->with('js_order')
            ->will($this->returnValue($jsOrder));
        $this->_request->expects($this->once(5))->method('getPost')->will($this->returnValue(true));

        $themeMock = $this->getMock('Magento\Core\Model\Theme',
            array('save', 'load', 'setCustomization', 'getThemeImage'), array(), '', false);

        $themeImage = $this->getMock('Magento\Core\Model\Theme\Image', array(), array(), '', false);
        $themeMock->expects($this->any())->method('getThemeImage')->will($this->returnValue($themeImage));

        $themeFactory = $this->getMock('Magento\Core\Model\Theme\FlyweightFactory', array('create'), array(), '',
            false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeMock));

        $this->_objectManagerMock
            ->expects($this->at(0))
            ->method('get')
            ->with('Magento\Core\Model\Theme\FlyweightFactory')
            ->will($this->returnValue($themeFactory));

        $this->_objectManagerMock
            ->expects($this->at(1))
            ->method('get')
            ->with('Magento\Theme\Model\Theme\Customization\File\CustomCss')
            ->will($this->returnValue(null));

        $this->_objectManagerMock
            ->expects($this->at(2))
            ->method('create')
            ->with('Magento\Theme\Model\Theme\SingleFile')
            ->will($this->returnValue(null));

        $this->_model->saveAction();
    }

}
