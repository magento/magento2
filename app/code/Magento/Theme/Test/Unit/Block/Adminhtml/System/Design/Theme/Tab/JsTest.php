<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Tab;

use Magento\Backend\Model\Url;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Js;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\TestCase;

class JsTest extends TestCase
{
    /**
     * @var Js
     */
    protected $_model;

    /**
     * @var Url
     */
    protected $_urlBuilder;

    protected function setUp(): void
    {
        $this->_urlBuilder = $this->createMock(Url::class);

        $objectManagerHelper = new ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            Js::class,
            [
                'formFactory' => $this->createMock(FormFactory::class),
                'objectManager' => $this->getMockForAbstractClass(ObjectManagerInterface::class),
                'urlBuilder' => $this->_urlBuilder
            ]
        );

        $this->_model = $this->getMockBuilder(Js::class)
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
        $class = new \ReflectionClass(Js::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetAdditionalElementTypes()
    {
        $method = $this->_getMethod('_getAdditionalElementTypes');
        $result = $method->invokeArgs($this->_model, []);
        $expectedResult = [
            'js_files' => File::class,
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
        $themeMock = $this->createPartialMock(Theme::class, ['isVirtual', 'getId', '__wakeup']);
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
