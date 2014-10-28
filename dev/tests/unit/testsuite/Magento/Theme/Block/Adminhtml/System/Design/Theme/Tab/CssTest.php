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

class CssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlCoder;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->urlBuilder = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);
        $this->urlCoder = $this->getMock('Magento\Framework\Encryption\UrlCoder', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css',
            array(
                'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', array(), array(), '', false),
                'objectManager' => $this->_objectManager,
                'uploaderService' => $this->getMock(
                        'Magento\Theme\Model\Uploader\Service',
                        array(),
                        array(),
                        '',
                        false
                    ),
                'urlBuilder' => $this->urlBuilder,
                'urlCoder' => $this->urlCoder
            )
        );

        $this->_model = $this->getMock(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css',
            array('_getCurrentTheme'),
            $constructArguments,
            '',
            true
        );
    }

    public function testGetUploadCssFileNote()
    {
        $method = self::getMethod('_getUploadCssFileNote');
        /** @var $sizeModel \Magento\Framework\File\Size */
        $sizeModel = $this->getMock('Magento\Framework\File\Size', null, array(), '', false);

        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'Magento\Framework\File\Size'
        )->will(
            $this->returnValue($sizeModel)
        );

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = 'Allowed file types *.css.<br />';
        $expectedResult .= 'This file will replace the current custom.css file and can\'t be more than 2 MB.<br />';
        $expectedResult .= sprintf('Max file size to upload %sM', $sizeModel->getMaxFileSizeInMb());
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetAdditionalElementTypes()
    {
        $method = self::getMethod('_getAdditionalElementTypes');

        /** @var $configModel \Magento\Framework\App\Config\ScopeConfigInterface */
        $configModel = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->will(
            $this->returnValue($configModel)
        );

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = array(
            'links' => 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links',
            'css_file' => 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File'
        );
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
        $class = new \ReflectionClass('Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css::getDownloadUrl
     */
    public function testGetterDownloadUrl()
    {
        $fileId = 1;
        $themeId = 1;
        $this->urlCoder->expects($this->atLeastOnce())->method('encode')->with($fileId)
            ->will($this->returnValue('encoded'));
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->anything(), ['theme_id' => $themeId, 'file' => 'encoded']);
        $this->_model->getDownloadUrl($fileId, $themeId);
    }
}
