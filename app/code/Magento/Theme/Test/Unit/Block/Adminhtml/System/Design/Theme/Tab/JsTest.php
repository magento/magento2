<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Tab;

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
        $this->_urlBuilder = $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js::class,
            [
                'formFactory' => $this->getMock(\Magento\Framework\Data\FormFactory::class, [], [], '', false),
                'objectManager' => $this->getMock(\Magento\Framework\ObjectManagerInterface::class),
                'urlBuilder' => $this->_urlBuilder
            ]
        );

        $this->_model = $this->getMock(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js::class,
            ['_getCurrentTheme'],
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
        $class = new \ReflectionClass(\Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetAdditionalElementTypes()
    {
        $method = $this->_getMethod('_getAdditionalElementTypes');
        $result = $method->invokeArgs($this->_model, []);
        $expectedResult = [
            'js_files' => \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File::class,
        ];
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
            \Magento\Theme\Model\Theme::class,
            ['isVirtual', 'getId', '__wakeup'],
            [],
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
            ['id' => $themeId]
        )->will(
            $this->returnValue($uploadUrl)
        );

        $this->assertEquals($uploadUrl, $this->_model->getJsUploadUrl());
    }

    public function testGetUploadJsFileNote()
    {
        $method = $this->_getMethod('_getUploadJsFileNote');
        $result = $method->invokeArgs($this->_model, []);
        $this->assertEquals('Allowed file types *.js.', $result);
    }
}
