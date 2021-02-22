<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Tab;

class JsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js
     */
    protected $_model;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlBuilder;

    protected function setUp(): void
    {
        $this->_urlBuilder = $this->createMock(\Magento\Backend\Model\Url::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js::class,
            [
                'formFactory' => $this->createMock(\Magento\Framework\Data\FormFactory::class),
                'objectManager' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
                'urlBuilder' => $this->_urlBuilder
            ]
        );

        $this->_model = $this->getMockBuilder(\Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js::class)
            ->setMethods(['_getCurrentTheme'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
    }

    protected function tearDown(): void
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
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['isVirtual', 'getId', '__wakeup']);
        $themeMock->expects($this->any())->method('getId')->willReturn($themeId);

        $this->_model->expects($this->any())->method('_getCurrentTheme')->willReturn($themeMock);

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_theme/uploadjs',
            ['id' => $themeId]
        )->willReturn(
            $uploadUrl
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
