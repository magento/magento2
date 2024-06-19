<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Tab;

use Magento\Backend\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\File\Size;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css;
use Magento\Theme\Model\Uploader\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CssTest extends TestCase
{
    /**
     * @var Css
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var MockObject
     */
    protected $urlBuilder;

    /**
     * @var MockObject
     */
    protected $urlCoder;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->urlBuilder = $this->createMock(Url::class);
        $this->urlCoder = $this->createMock(UrlCoder::class);

        $objectManagerHelper = new ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            Css::class,
            [
                'formFactory' => $this->createMock(FormFactory::class),
                'objectManager' => $this->_objectManager,
                'uploaderService' => $this->createMock(Service::class),
                'urlBuilder' => $this->urlBuilder,
                'urlCoder' => $this->urlCoder
            ]
        );

        $this->_model = $this->getMockBuilder(Css::class)
            ->onlyMethods(['_getCurrentTheme'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
    }

    public function testGetUploadCssFileNote()
    {
        $method = self::getMethod('_getUploadCssFileNote');
        /** @var $sizeModel \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\File\Size  */
        $sizeModel = $this->createMock(Size::class);
        $sizeModel->expects($this->any())->method('getMaxFileSizeInMb')->willReturn('2M');

        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            Size::class
        )->willReturn(
            $sizeModel
        );

        $result = $method->invokeArgs($this->_model, []);
        $expectedResult = 'Allowed file types *.css.<br />';
        $expectedResult .= 'This file will replace the current custom.css file and can\'t be more than 2 MB.<br />';
        $expectedResult .= sprintf('Max file size to upload %sM', $sizeModel->getMaxFileSizeInMb());
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetAdditionalElementTypes()
    {
        $method = self::getMethod('_getAdditionalElementTypes');

        /** @var ScopeConfigInterface $configModel */
        $configModel = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            ScopeConfigInterface::class
        )->willReturn(
            $configModel
        );

        $result = $method->invokeArgs($this->_model, []);
        $expectedResult = [
            'links' => Links::class,
            'css_file' => File::class,
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('CSS Editor', $this->_model->getTabLabel());
    }

    /**
     * @param string $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass(Css::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * cover \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css::getDownloadUrl
     */
    public function testGetterDownloadUrl()
    {
        $fileId = 1;
        $themeId = 1;
        $this->urlCoder->expects($this->atLeastOnce())->method('encode')->with($fileId)
            ->willReturn('encoded');
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->anything(), ['theme_id' => $themeId, 'file' => 'encoded']);
        $this->_model->getDownloadUrl($fileId, $themeId);
    }
}
