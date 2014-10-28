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
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Tab;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js
     */
    protected $_model;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlBuilder;

    protected function setUp()
    {
        $this->_urlBuilder = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js',
            array(
                'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', array(), array(), '', false),
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false),
                'urlBuilder' => $this->_urlBuilder
            )
        );

        $this->_model = $this->getMock(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js',
            array('_getCurrentTheme'),
            $constructArguments,
            '',
            true
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param string $name
     * @return \ReflectionMethod
     */
    protected function _getMethod($name)
    {
        $class = new \ReflectionClass('Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetAdditionalElementTypes()
    {
        $method = $this->_getMethod('_getAdditionalElementTypes');
        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = array(
            'js_files' => 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File'
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('JS Editor', $this->_model->getTabLabel());
    }

    public function testGetJsUploadUrl()
    {
        $themeId = 2;
        $uploadUrl = 'upload_url';
        $themeMock = $this->getMock(
            'Magento\Core\Model\Theme',
            array('isVirtual', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $themeMock->expects($this->any())->method('getId')->will($this->returnValue($themeId));

        $this->_model->expects($this->any())->method('_getCurrentTheme')->will($this->returnValue($themeMock));

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_theme/uploadjs',
            array('id' => $themeId)
        )->will(
            $this->returnValue($uploadUrl)
        );

        $this->assertEquals($uploadUrl, $this->_model->getJsUploadUrl());
    }

    public function testGetUploadJsFileNote()
    {
        $method = $this->_getMethod('_getUploadJsFileNote');
        $result = $method->invokeArgs($this->_model, array());
        $this->assertEquals('Allowed file types *.js.', $result);
    }
}
